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

$sql = " SELECT
    ISNULL(
        ISNULL(
            CASE
                WHEN dbo.TB02115.TB02115_TIPOINTERV = 'I' THEN TECFECH.TB01024_NOME
                ELSE PRESTFECH.TB01017_NOME
            END,
            dbo.TB01024.TB01024_NOME
        ),
        'OS sem técnico vinculado'
    ) AS Tecnico,

    dbo.TB02115.TB02115_CODIGO AS OS,

    SUBSTRING(CONVERT(VARCHAR(10), dbo.TB02115.TB02115_DTFECHA, 103), 7, 4) + '-' + SUBSTRING(CONVERT(VARCHAR(10), dbo.TB02115.TB02115_DTFECHA, 103), 4, 2) AS AnoMes,

    CAST(CAST(dbo.TB02115.TB02115_DTFECHA AS date) AS varchar(10)) AS DataB,
    CAST(dbo.TB02115.TB02115_DTFECHA AS date) AS DataFechamento,

    CASE
        WHEN dbo.TB02115.TB02115_PREVENTIVA = 'I' THEN 'Instalação'
        WHEN dbo.TB02115.TB02115_PREVENTIVA = 'N' THEN 'Normal'
        WHEN dbo.TB02115.TB02115_PREVENTIVA = 'D' THEN 'Desinstalação'
        WHEN dbo.TB02115.TB02115_PREVENTIVA = 'P' THEN 'Preventiva'
        WHEN dbo.TB02115.TB02115_PREVENTIVA = 'R' THEN 'Retorno / Recarga'
        ELSE 'Aferição'
    END AS Tipo,

    dbo.TB02115.TB02115_CODCLI AS CodCli,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM IN ('C', 'B') THEN dbo.TB01008.TB01008_NOME
        ELSE 'EQUIPAMENTO INTERNO'
    END AS Cliente,

    dbo.TB01007.TB01007_NOME AS Empresa,

    CASE
        CASE
            WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02111.TB02111_TIPOCONTR
            WHEN dbo.TB02115.TB02115_ORIGEM = 'B' THEN 'B'
            ELSE 'I'
        END
        WHEN 'A' THEN 'Avulso'
        WHEN 'G' THEN 'Garantia'
        WHEN 'M' THEN 'Manutenção'
        WHEN 'L' THEN 'Locação'
        ELSE
            CASE
                WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02111.TB02111_TIPOCONTR
                WHEN dbo.TB02115.TB02115_ORIGEM = 'B' THEN 'B'
                ELSE 'I'
            END
    END AS TipoContrato,

    dbo.TB01010.TB01010_NOME AS Equipamento,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02112.TB02112_CIDADE
        ELSE dbo.TB00012.TB00012_CIDADE
    END AS Cidade,

    Defeito.NOME AS Defeito,

    ISNULL(
        CASE
            WHEN FT02020.CALC_RESTANTE < 0 THEN 1
            ELSE 0
        END,
        0
    ) AS SLA_Excedido,

    ISNULL(
        DATEDIFF(
            MI,
            CAST(
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAINI, 103), 7, 7) + '-' +
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAINI, 103), 4, 2) + '-' +
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAINI, 103), 1, 2) + ' ' +
                TB02122_DADOS.TB02122_HORAINI + ':00' AS datetime
            ),
            CAST(
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAFIM, 103), 7, 7) + '-' +
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAFIM, 103), 4, 2) + '-' +
                SUBSTRING(CONVERT(VARCHAR(10), TB02122_DADOS.TB02122_DATAFIM, 103), 1, 2) + ' ' +
                TB02122_DADOS.TB02122_HORAFIM + ':00' AS datetime
            )
        ),
        0
    ) AS ProdSegundos,

    ISNULL(
        CAST(
            CASE
                WHEN ISNULL(TB02122_DADOS.TB02122_KMFINAL, 0) - ISNULL(TB02122_DADOS.TB02122_KMINICIAL, 0) = 0 THEN 0
                ELSE ISNULL(TB02122_DADOS.TB02122_KMFINAL, 0) - ISNULL(TB02122_DADOS.TB02122_KMINICIAL, 0) * 0.028
            END AS int
        ),
        0
    ) AS TempoDeslocSeg,

    ISNULL(ValorPecasTrocadas.CUSTO, 0) AS ValorPecasTrocadas,

    ISNULL(
        CASE
            WHEN dbo.TB02115.TB02115_REINCIDENCIA = 'S' THEN 1
            ELSE 0
        END,
        0
    ) AS Reincidencia,

    ISNULL(
        CASE
            WHEN DATEDIFF(D, (DT.DT), dbo.TB02115.TB02115_DATA) <= 30 THEN 1
            ELSE 0
        END,
        0
    ) AS Retorno,

    ISNULL(
        CAST(
            ISNULL(TB02122_DADOS.TB02122_KMFINAL, 0) - ISNULL(TB02122_DADOS.TB02122_KMINICIAL, 0) AS numeric(11, 3)
        ),
        0
    ) AS KMSaldo

FROM dbo.TB02115
    LEFT JOIN dbo.TB02112 ON dbo.TB02115.TB02115_CONTRATO = dbo.TB02112.TB02112_CODIGO
        AND dbo.TB02115.TB02115_PRODUTO = dbo.TB02112.TB02112_PRODUTO
        AND dbo.TB02115.TB02115_NUMSERIE = dbo.TB02112.TB02112_NUMSERIE
    LEFT JOIN dbo.TB01008 ON dbo.TB02115.TB02115_CODCLI = dbo.TB01008.TB01008_CODIGO
    LEFT JOIN dbo.TB01010 ON dbo.TB02115.TB02115_PRODUTO = dbo.TB01010.TB01010_CODIGO
    LEFT JOIN dbo.TB01024 ON dbo.TB02115.TB02115_CODTEC = dbo.TB01024.TB01024_CODIGO
    LEFT JOIN dbo.TB01024 AS TB01024_2 ON dbo.TB02112.TB02112_CODTEC = TB01024_2.TB01024_CODIGO
    LEFT JOIN dbo.TB01017 ON dbo.TB02115.TB02115_PREST = dbo.TB01017.TB01017_CODIGO
    LEFT JOIN dbo.TB01007 ON dbo.TB02115.TB02115_CODEMP = dbo.TB01007.TB01007_CODIGO
    LEFT JOIN dbo.TB00012 ON dbo.TB00012.TB00012_CODIGO = dbo.TB02115.TB02115_CODEMP
        AND TB00012_TABELA = 'TB01007'
        AND TB00012_TIPO = '01'
    LEFT JOIN dbo.TB02111 ON dbo.TB02111.TB02111_CODIGO = dbo.TB02115.TB02115_CONTRATO
    LEFT JOIN dbo.TB01006 ON dbo.TB02111.TB02111_VEND = dbo.TB01006.TB01006_CODIGO
    LEFT JOIN dbo.TB02122 ON dbo.TB02122.TB02122_NUMOS = dbo.TB02115.TB02115_CODIGO
    LEFT JOIN dbo.TB01055 ON dbo.TB01055.TB01055_CODIGO = dbo.TB02122.TB02122_CONDICAO
    LEFT JOIN dbo.TB01073 ON dbo.TB01073.TB01073_CODIGO = dbo.TB02115.TB02115_STATUS
    LEFT JOIN dbo.TB01066 ON dbo.TB01066.TB01066_TECNICO = dbo.TB02115.TB02115_CODTEC
    LEFT JOIN dbo.TB02176 ON dbo.TB02176.TB02176_CODIGO = dbo.TB02115.TB02115_CODSITE
    LEFT JOIN dbo.TB02177 ON dbo.TB02177.TB02177_CODIGO = dbo.TB02115.TB02115_CODDEPTO
    LEFT JOIN dbo.TB02054 ON dbo.TB02054.TB02054_CODEMP = dbo.TB02115.TB02115_CODEMP
        AND dbo.TB02054.TB02054_NUMSERIE = dbo.TB02115.TB02115_NUMSERIE
        AND dbo.TB02054.TB02054_PRODUTO = dbo.TB02115.TB02115_PRODUTO
    LEFT JOIN dbo.TB01098 ON dbo.TB01098.TB01098_CIDADE = dbo.TB02115.TB02115_CIDADE
        AND dbo.TB01098.TB01098_ESTADO = dbo.TB02115.TB02115_ESTADO
    LEFT JOIN dbo.TB01097 ON dbo.TB01098.TB01098_AREA = dbo.TB01097.TB01097_CODIGO
    LEFT JOIN dbo.TB00009 ON dbo.TB00009.TB00009_CODIGO = dbo.TB02112.TB02112_MODULO
    LEFT JOIN dbo.TB01107 ON dbo.TB01107.TB01107_CODIGO = dbo.TB01008.TB01008_GRUPO
    LEFT JOIN dbo.TB01003 ON dbo.TB01003.TB01003_CODIGO = dbo.TB02115.TB02115_LOCPPROD

    LEFT JOIN (
        SELECT
            A.TB02018_CODIGO,
            A.TB02018_DATA,
            A.TB02018_OS,
            A.ORDEM
        FROM
            (
                SELECT
                    TB18_1.TB02018_CODIGO,
                    TB18_1.TB02018_DATA,
                    TB18_1.TB02018_OS,
                    ROW_NUMBER() OVER (
                        PARTITION BY TB18_1.TB02018_OS
                        ORDER BY
                            TB18_1.TB02018_CODIGO DESC
                    ) AS ORDEM
                FROM
                    TB02018 AS TB18_1
                WHERE
                    TB18_1.TB02018_DATA = (
                        SELECT
                            MAX(TB18_2.TB02018_DATA)
                        FROM
                            TB02018 AS TB18_2
                        WHERE
                            TB18_2.TB02018_OS = TB18_1.TB02018_OS
                    )
            ) A
        WHERE
            A.ORDEM = 1
    ) TB02018 ON TB02018.TB02018_OS = dbo.TB02115.TB02115_CODIGO

    LEFT JOIN (
        SELECT
            A.TB02021_CODIGO,
            A.TB02021_DATA,
            A.TB02021_OS,
            A.ORDEM
        FROM
            (
                SELECT
                    TB18_1.TB02021_CODIGO,
                    TB18_1.TB02021_DATA,
                    TB18_1.TB02021_OS,
                    ROW_NUMBER() OVER (
                        PARTITION BY TB18_1.TB02021_OS
                        ORDER BY
                            TB18_1.TB02021_CODIGO DESC
                    ) AS ORDEM
                FROM
                    TB02021 AS TB18_1
                WHERE
                    TB18_1.TB02021_DATA = (
                        SELECT
                            MAX(TB18_2.TB02021_DATA)
                        FROM
                            TB02021 AS TB18_2
                        WHERE
                            TB18_2.TB02021_OS = TB18_1.TB02021_OS
                    )
            ) A
        WHERE
            A.ORDEM = 1
    ) TB02021 ON TB02021.TB02021_OS = dbo.TB02115.TB02115_CODIGO

    OUTER APPLY dbo.FT02020(
        dbo.TB02115.TB02115_CODIGO,
        dbo.TB02115.TB02115_PREVENTIVA,
        dbo.TB02115.TB02115_DATA,
        dbo.TB02115.TB02115_DTFECHA,
        TB01008_HORAS,
        CASE
            WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02111.TB02111_TIPOCONTR
            WHEN dbo.TB02115.TB02115_ORIGEM = 'B' THEN 'B'
            ELSE 'I'
        END,
        CASE
            WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02111.TB02111_CONTROLESLA
            ELSE NULL
        END,
        dbo.TB02112.TB02112_HORAS,
        dbo.TB02112.TB02112_TIPOHORAS,
        dbo.TB02112.TB02112_TIPOSLA,
        dbo.TB02112.TB02112_CODSITE,
        'A'
    ) AS FT02020

    LEFT JOIN dbo.TB02122 AS TB02122_DADOS ON TB02122_DADOS.TB02122_NUMOS = dbo.TB02115.TB02115_CODIGO
        AND TB02122_DADOS.TB02122_CODCLI = dbo.TB02115.TB02115_CODCLI
        AND TB02122_DADOS.TB02122_CONTRATO = dbo.TB02115.TB02115_CONTRATO
        AND TB02122_DADOS.TB02122_NUMSERIE = dbo.TB02115.TB02115_NUMSERIE
    LEFT JOIN dbo.TB01024 AS TECFECH ON TECFECH.TB01024_CODIGO = TB02122_DADOS.TB02122_CODTEC
    LEFT JOIN dbo.TB01017 AS PRESTFECH ON PRESTFECH.TB01017_CODIGO = TB02122_DADOS.TB02122_PREST

    OUTER APPLY (
        SELECT
            ISNULL(SUM(TB02125_TOTVALOR), 0) AS TOTVALOR
        FROM
            dbo.TB02125
        WHERE
            TB02125_CODIGO = dbo.TB02122.TB02122_CODIGO
    ) AS SERV

    OUTER APPLY (
        SELECT
            ISNULL(SUM(TB02124_CUSTO), 0) AS CUSTO
        FROM
            dbo.TB02124
        WHERE
            TB02124_CODIGO = dbo.TB02122.TB02122_CODIGO
    ) AS ValorPecasTrocadas

    OUTER APPLY (
        SELECT
            TOP 1 TB02115_ANT.TB02115_DATA AS DT
        FROM
            dbo.TB02115 AS TB02115_ANT
        WHERE
            TB02115_ANT.TB02115_NUMSERIE = dbo.TB02115.TB02115_NUMSERIE
            AND CAST(TB02115_ANT.TB02115_CODIGO AS int) < CAST(dbo.TB02115.TB02115_CODIGO AS int)
        ORDER BY
            TB02115_ANT.TB02115_DATA DESC
    ) AS DT

    OUTER APPLY (
        SELECT
            TOP 1 TB01048_NOME AS NOME
        FROM
            dbo.TB01048
            LEFT JOIN dbo.TB02116 ON TB02116_DEFEITO = TB01048_CODIGO
        WHERE
            TB02116_CODIGO = dbo.TB02115.TB02115_CODIGO
    ) AS Defeito

    OUTER APPLY (
        SELECT
            prodseg
        FROM
            dbo.BM_PRODTEC
        WHERE
            [data] = CAST(dbo.TB02115.TB02115_DATA AS date)
            AND codtec = dbo.TB02115.TB02115_CODTEC
    ) AS prodseg
WHERE
    dbo.TB02115.TB02115_SITUACAO = 'A'
	AND dbo.TB02115.TB02115_DTFECHA IS NOT NULL
	AND CAST(dbo.TB02115.TB02115_DTFECHA as date) BETWEEN :dt_inicio AND :dt_fim
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
