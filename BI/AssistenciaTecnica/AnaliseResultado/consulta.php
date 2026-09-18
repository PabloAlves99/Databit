<?php
header('Content-Type: application/json; charset=utf-8');
require_once('config.php');

$host = $server;
$dbname = $base;
$user = $usuarioBanco;
$pass = $SenhaBanco;

try {
    $conn = new PDO("sqlsrv:Server=$host;Database=$dbname", $user, $pass, [
        PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
    ]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Falha na conexão com o banco de dados."]);
    exit;
}

$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

$sql = "WITH DadosBase AS (
	-- [ INICIO DA SUA CONSULTA ORIGINAL ]
	SELECT
		TB02115_CODIGO OS,
		CASE
			WHEN TB02115_PREVENTIVA = 'I' THEN 'Instalação'
			WHEN TB02115_PREVENTIVA = 'N' THEN 'Normal'
			WHEN TB02115_PREVENTIVA = 'D' THEN 'Desinstalação'
			WHEN TB02115_PREVENTIVA = 'P' THEN 'Preventiva'
			WHEN TB02115_PREVENTIVA = 'R' THEN 'Retorno / Recarga'
			ELSE 'Aferição'
		END Tipo,
		CASE
			TB02111_TIPOCONTR
			WHEN 'A' THEN 'Avulso'
			WHEN 'G' THEN 'Garantia'
			WHEN 'M' THEN 'Manutenção'
			WHEN 'L' THEN 'Locação'
			ELSE TB02111_TIPOCONTR
		END TipoContrato,
		TB02115_CODEMP Codemp,
		TB01007_NOME Empresa,
		TB02115_DATA [Data],
		SUBSTRING(CONVERT(VARCHAR(10), TB02115_DATA, 103), 1, 10) DataB,
		SUBSTRING(CONVERT(VARCHAR(10), TB02115_DATA, 103), 7, 4) + '-' + SUBSTRING(CONVERT(VARCHAR(10), TB02115_DATA, 103), 4, 2) AnoMes,
		SUBSTRING(CONVERT(VARCHAR(10), TB02115_DTFECHA, 103), 7, 4) + '-' + SUBSTRING(CONVERT(VARCHAR(10), TB02115_DTFECHA, 103), 4, 2) AnoMesFechamento,
		TB02115_CODCLI CodCli,
		TB01008_NOME Cliente,
		TB02115_CONTRATO Contrato,
		TB02111_NOME Classificação,
		TB02115_NUMSERIE Serie,
		TB02112_PAT Patromônio,
		TB01010_NOME Equipamento,
		VW02095.TB02115_CODTEC CodTec,
		ISNULL(
			ISNULL(
				CASE
					WHEN TB02115_TIPOINTERV = 'I' THEN TECFECH.TB01024_NOME
					ELSE PRESTFECH.TB01017_NOME
				END,
				VW02095.TB01024_NOME
			),
			'OS sem técnico vinculado'
		) Tecnico,
		CALC_PREVISAO SLA,
		CALC_RESTANTE Previsao,
		CASE
			WHEN CALC_RESTANTE < 0 THEN 1
			ELSE 0
		END SLA_Excedido,
		CAST(TB02115_DTFECHA as date) DataFechamento,
		TB01073_NOME [Status],
		CAST(
			SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
				7,
				7
			) + '-' + SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
				4,
				2
			) + '-' + SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
				1,
				2
			) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
		) DataHoraIniTIME,
		CAST(
			SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
				7,
				7
			) + '-' + SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
				4,
				2
			) + '-' + SUBSTRING(
				CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
				1,
				2
			) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
		) DataHoraFimTIME,
		CONVERT(
			VARCHAR(20),
			DATEDIFF(
				MI,
				CAST(
					SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
						7,
						7
					) + '-' + SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
						4,
						2
					) + '-' + SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
						1,
						2
					) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
				),
				CAST(
					SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
						7,
						7
					) + '-' + SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
						4,
						2
					) + '-' + SUBSTRING(
						CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
						1,
						2
					) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
				)
			) / 60
		) + ':' + RIGHT(
			REPLICATE('0', 2) + CONVERT(
				VARCHAR(10),
				DATEDIFF(
					MI,
					CAST(
						SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
							7,
							7
						) + '-' + SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
							4,
							2
						) + '-' + SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
							1,
							2
						) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
					),
					CAST(
						SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
							7,
							7
						) + '-' + SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
							4,
							2
						) + '-' + SUBSTRING(
							CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
							1,
							2
						) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
					)
				) % 60
			),
			2
		) TesteProduzido,
		DATEDIFF(
			MI,
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
			),
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
			)
		) ProdSegundos,
		TB02112_HORAS SLA_Contrato,
		SERV.TOTVALOR ValorServicos,
		ValorPecasTrocadas.CUSTO ValorPecasTrocadas,
		CASE
			WHEN TB02115_REINCIDENCIA = 'S' THEN 1
			ELSE 0
		END Reincidência,
		CASE
			WHEN DATEDIFF(D, (DT.DT), VW02095.TB02115_DATA) <= 30 THEN 1
			ELSE 0
		END Retorno,
		VW02095.TB02122_CODIGO CodFech,
		TB02112_CIDADE Cidade,
		TB02112_ESTADO Estado,
		VW02095.TB02176_NOME [Site],
		VW02095.TB02177_NOME Departamento,
		CAST(
			ISNULL(dbo.TB02122.TB02122_KMFINAL, 0) - ISNULL(dbo.TB02122.TB02122_KMINICIAL, 0) as numeric(11, 3)
		) KMSaldo,
		CAST(
			CASE
				WHEN ISNULL(dbo.TB02122.TB02122_KMFINAL, 0) - ISNULL(dbo.TB02122.TB02122_KMINICIAL, 0) = 0 THEN 0
				ELSE ISNULL(dbo.TB02122.TB02122_KMFINAL, 0) - ISNULL(dbo.TB02122.TB02122_KMINICIAL, 0) * 0.028
			END as int
		) TempoDeslocSeg,
		dbo.TB02122.TB02122_PLACA Placa,
		Defeito.NOME Defeito,
		ISNULL(TB02122.TB02122_CONTADOR, 0) MedidorPB,
		ISNULL(TB02122.TB02122_CONTADORC, 0) MedidorCor,
		ISNULL(TB02122.TB02122_CONTADORGF, 0) MedidorA3PB,
		ISNULL(TB02122.TB02122_CONTADORGFC, 0) MedidorA3Cor,
		ISNULL(TB02122.TB02122_CONTADORDG, 0) MedidorA3DG,
		TB01055_NOME CondIntervencao,
		1 Qtde,
		ISNULL(
			CAST(TECFECH.TB01024_OBS as varchar(20)),
			'Sem equipe no cadastro'
		) Equipe,
		ISNULL(prodseg.prodseg, 0) TempoPontoSeg,
		DATEDIFF(
			SS,
			TB02115_DATA,
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
			)
		) RespSlaSeg,
		DATEDIFF(
			SS,
			TB02115_DATA,
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
			)
		) RespTecSlaSeg,
		DATEDIFF(
			SS,
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAINI, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAINI + ':00' as datetime
			),
			CAST(
				SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					7,
					7
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					4,
					2
				) + '-' + SUBSTRING(
					CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 103),
					1,
					2
				) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' as datetime
			)
		) RespMediaTec
	FROM
		VW02095
		LEFT JOIN TB02122 ON dbo.TB02122.TB02122_NUMOS = VW02095.TB02115_CODIGO
		AND dbo.TB02122.TB02122_CODCLI = VW02095.TB02115_CODCLI
		AND dbo.TB02122.TB02122_CONTRATO = VW02095.TB02115_CONTRATO
		AND dbo.TB02122.TB02122_NUMSERIE = VW02095.TB02115_NUMSERIE
		LEFT JOIN TB01024 AS TECFECH ON TECFECH.TB01024_CODIGO = dbo.TB02122.TB02122_CODTEC
		LEFT JOIN TB01017 AS PRESTFECH ON PRESTFECH.TB01017_CODIGO = dbo.TB02122.TB02122_PREST
		OUTER APPLY (
			SELECT
				ISNULL(SUM(TB02125_TOTVALOR), 0) TOTVALOR
			FROM
				TB02125
			WHERE
				TB02125_CODIGO = VW02095.TB02122_CODIGO
		) SERV
		OUTER APPLY (
			SELECT
				ISNULL(SUM(TB02124_CUSTO), 0) CUSTO
			FROM
				TB02124
			WHERE
				TB02124_CODIGO = VW02095.TB02122_CODIGO
		) ValorPecasTrocadas
		OUTER APPLY (
			SELECT
				TOP 1 dbo.TB02115.TB02115_DATA DT
			FROM
				TB02115
			WHERE
				dbo.TB02115.TB02115_NUMSERIE = VW02095.TB02115_NUMSERIE
				AND CAST(dbo.TB02115.TB02115_CODIGO as int) < CAST(VW02095.TB02115_CODIGO as int)
			ORDER BY
				dbo.TB02115.TB02115_DATA DESC
		) DT
		OUTER APPLY (
			SELECT
				TOP 1 TB01048_NOME NOME
			FROM
				TB01048
				LEFT JOIN TB02116 ON TB02116_DEFEITO = TB01048_CODIGO
			WHERE
				TB02116_CODIGO = VW02095.TB02115_CODIGO
		) Defeito
		OUTER APPLY (
			SELECT
				prodseg
			FROM
				BM_PRODTEC
			WHERE
				[data] = cast(VW02095.TB02115_DATA as date)
				AND codtec = VW02095.TB02115_CODTEC
		) prodseg
	WHERE
		TB02115_situacao = 'A'
		AND TB02115_DTFECHA IS NOT NULL
		AND CAST(TB02115_DTFECHA as date) BETWEEN :dt_inicio
		AND :dt_fim
) -- INCLUI TODAS AS COLUNAS DOS FILTROS AQUI
SELECT
	Tecnico,
	OS,
	AnoMesFechamento AS AnoMes,
	CAST(DataFechamento as varchar(10)) AS DataB,
	Tipo,
	CodCli,
	Cliente,
	Empresa,
	TipoContrato,
	Equipamento,
	Cidade,
	Defeito,
	ISNULL(SLA_Excedido, 0) AS SLA_Excedido,
	ISNULL(ProdSegundos, 0) AS ProdSegundos,
	ISNULL(TempoDeslocSeg, 0) AS TempoDeslocSeg,
	ISNULL(ValorPecasTrocadas, 0) AS ValorPecasTrocadas,
	ISNULL(Reincidência, 0) AS Reincidencia,
	ISNULL(Retorno, 0) AS Retorno,
	ISNULL(KMSaldo, 0) AS KMSaldo
FROM
	DadosBase
ORDER BY
	Tecnico,
	OS;
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':dt_inicio', $data_inicio);
    $stmt->bindParam(':dt_fim', $data_fim);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dadosLimpos = [];

    // O PHP apenas limpa os acentos e garante que valores NULOS virem textos vazios
    foreach ($resultados as $row) {
        $dadosLimpos[] = [
            "Tecnico" => mb_convert_encoding($row['Tecnico'] ?? '', 'UTF-8', 'auto'),
            "OS" => mb_convert_encoding($row['OS'] ?? '', 'UTF-8', 'auto'),
            "AnoMes" => mb_convert_encoding($row['AnoMes'] ?? '', 'UTF-8', 'auto'),
            "DataB" => mb_convert_encoding($row['DataB'] ?? '', 'UTF-8', 'auto'),
            "Tipo" => mb_convert_encoding($row['Tipo'] ?? '', 'UTF-8', 'auto'),
            "CodCli" => mb_convert_encoding($row['CodCli'] ?? '', 'UTF-8', 'auto'),
            "Cliente" => mb_convert_encoding($row['Cliente'] ?? '', 'UTF-8', 'auto'),
            "Empresa" => mb_convert_encoding($row['Empresa'] ?? '', 'UTF-8', 'auto'),
            "TipoContrato" => mb_convert_encoding($row['TipoContrato'] ?? '', 'UTF-8', 'auto'),
            "Equipamento" => mb_convert_encoding($row['Equipamento'] ?? '', 'UTF-8', 'auto'),
            "Cidade" => mb_convert_encoding($row['Cidade'] ?? '', 'UTF-8', 'auto'),
            "Defeito" => mb_convert_encoding($row['Defeito'] ?? '', 'UTF-8', 'auto'),
            "SLA_Excedido" => (int)$row['SLA_Excedido'],
            "ProdSegundos" => (int)$row['ProdSegundos'],
            "TempoDeslocSeg" => (int)$row['TempoDeslocSeg'],
            "ValorPecasTrocadas" => (float)$row['ValorPecasTrocadas'],
            "Reincidencia" => (int)$row['Reincidencia'],
            "Retorno" => (int)$row['Retorno'],
            "KMSaldo" => (float)$row['KMSaldo'] // <-- KM Rodado adicionado aqui
        ];
    }

    echo json_encode($dadosLimpos);
} catch (PDOException $e) {
    echo json_encode(["erro" => "Falha ao executar consulta.", "detalhes" => $e->getMessage()]);
}
