SELECT
    -- Identificação
    dbo.TB02115.TB02115_CODIGO AS os,
    dbo.TB02115.TB02115_CONTRATO AS contrato,
    dbo.TB02115.TB02115_CODCLI AS codcli,
    dbo.TB02115.TB02115_PRODUTO AS codprod,
    dbo.TB02115.TB02115_NUMSERIE AS numserie,
    dbo.TB02115.TB02115_CODEMP AS codemp,
    dbo.TB01024.TB01024_CODIGO AS codtec,
    CalcDefeito.CODIGO AS coddefeito,
    dbo.TB01055.TB01055_CODIGO AS codintervencao,

    -- Dados da OS
    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM IN ('C', 'B')
            THEN dbo.TB01008.TB01008_NOME
        ELSE 'EQUIPAMENTO INTERNO'
    END AS nomecli,
    dbo.TB01010.TB01010_NOME AS nomeprod,
    dbo.TB01007.TB01007_NOME AS nomeemp,
    dbo.TB01024.TB01024_NOME AS tecnico,
    CalcDefeito.Defeito AS defeito,
    dbo.TB01055.TB01055_NOME AS condintervencao,
    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C'
            THEN dbo.TB02112.TB02112_CIDADE
        ELSE dbo.TB00012.TB00012_CIDADE
    END AS cidade,
    dbo.TB02115.TB02115_ATENDENTE AS atendente,
    dbo.TB02115.TB02115_DATA AS [data],
    dbo.TB02115.TB02115_DTFECHA AS dtfecha,
    dbo.TB02122.TB02122_KMINICIAL AS kminicial,
    dbo.TB02122.TB02122_KMFINAL AS kmfinal,
    CASE
        WHEN UPPER(dbo.TB02115.TB02115_PREVENTIVA) = 'I' THEN 'Instalação'
        WHEN UPPER(dbo.TB02115.TB02115_PREVENTIVA) = 'N' THEN 'Normal'
        WHEN UPPER(dbo.TB02115.TB02115_PREVENTIVA) = 'D' THEN 'Desinstalação'
        WHEN UPPER(dbo.TB02115.TB02115_PREVENTIVA) = 'P' THEN 'Preventiva'
        WHEN UPPER(dbo.TB02115.TB02115_PREVENTIVA) = 'R' THEN 'Retorno / Recarga'
        ELSE 'Aferição'
    END AS tipo,

    -- Cálculos
    CalcSLA.SLA_Estourado_Segundos AS slaestouradosegundos,

    CASE
        WHEN LEN(CAST(CalcSLA.SLA_Estourado_Segundos / 3600 AS varchar(20))) = 1
            THEN '0' + CAST(CalcSLA.SLA_Estourado_Segundos / 3600 AS varchar(20))
        ELSE CAST(CalcSLA.SLA_Estourado_Segundos / 3600 AS varchar(20))
    END
        + ':' + RIGHT(
            '00' + CAST((CalcSLA.SLA_Estourado_Segundos % 3600) / 60 AS varchar(2)),
            2
        )
        + ':' + RIGHT(
            '00' + CAST(CalcSLA.SLA_Estourado_Segundos % 60 AS varchar(2)),
            2
        ) AS timeslanegative,

    CASE
        WHEN CALC_PREVISAO < CAST(
            CONVERT(varchar(10), dbo.TB02122.TB02122_DATAFIM, 23)
            + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' AS datetime
        ) THEN 1
        ELSE 0
    END AS slaexcedido,

    CASE
        WHEN DATEDIFF(
            DAY,
            ISNULL(HistoricoOS.DataAnterior, '19900101'),
            dbo.TB02115.TB02115_DATA
        ) <= 30 THEN 1
        ELSE 0
    END AS reincidenciaint,

    CONCAT(
        CASE WHEN CalcOS.MinutosTempo / 60 < 10 THEN '0' ELSE '' END,
        CalcOS.MinutosTempo / 60,
        ':',
        RIGHT('00' + CAST(CalcOS.MinutosTempo % 60 AS varchar(2)), 2)
    ) AS tempoatendimento,

    CONCAT(
        CASE WHEN CalcOS.MinutosTempo / 60 < 10 THEN '0' ELSE '' END,
        CalcOS.MinutosTempo / 60,
        ':',
        RIGHT('00' + CAST(CalcOS.MinutosTempo % 60 AS varchar(2)), 2)
    ) AS tempodeslocamento,

    dbo.TB02122.TB02122_KMFINAL
        - dbo.TB02122.TB02122_KMINICIAL AS kmsaldo,

    CAST(dbo.TB02115.TB02115_DATA AS date) AS datatrabalho,
    CalcOS.CustoTecnico AS custotecnico,
    CalcOS.CustoDeslocamento AS custodeslocamento,
    CalcOS.CustoPecas AS custopecas,

    CONVERT(
        varchar(5),
        DATEADD(
            SECOND,
            CONVERT(int, CONVERT(bigint, ROUND(CalcOS.SegundosMedios, 0)) % 86400),
            '19000101'
        ),
        108
    ) AS tempomedioatendsla

FROM dbo.TB02115
LEFT JOIN dbo.TB02112 ON dbo.TB02115.TB02115_CONTRATO = dbo.TB02112.TB02112_CODIGO
    AND dbo.TB02115.TB02115_PRODUTO = dbo.TB02112.TB02112_PRODUTO
    AND dbo.TB02115.TB02115_NUMSERIE = dbo.TB02112.TB02112_NUMSERIE
LEFT JOIN dbo.TB01008 ON dbo.TB02115.TB02115_CODCLI = dbo.TB01008.TB01008_CODIGO
LEFT JOIN dbo.TB01010 ON dbo.TB02115.TB02115_PRODUTO = dbo.TB01010.TB01010_CODIGO
LEFT JOIN dbo.TB01024 ON dbo.TB02115.TB02115_CODTEC = dbo.TB01024.TB01024_CODIGO
LEFT JOIN dbo.TB01007 ON dbo.TB02115.TB02115_CODEMP = dbo.TB01007.TB01007_CODIGO
LEFT JOIN dbo.TB00012 ON dbo.TB00012.TB00012_CODIGO = dbo.TB02115.TB02115_CODEMP
    AND TB00012_TABELA = 'TB01007'
    AND TB00012_TIPO = '01'
LEFT JOIN dbo.TB02111 ON dbo.TB02111.TB02111_CODIGO = dbo.TB02115.TB02115_CONTRATO
LEFT JOIN dbo.TB02122 ON dbo.TB02122.TB02122_NUMOS = dbo.TB02115.TB02115_CODIGO
LEFT JOIN dbo.TB01055 ON dbo.TB01055.TB01055_CODIGO = dbo.TB02122.TB02122_CONDICAO

OUTER APPLY dbo.FT02020(
    TB02115_CODIGO,
    TB02115_PREVENTIVA,
    TB02115_DATA,
    TB02115_DTFECHA,
    TB01008_HORAS,
    TB02111_TIPOCONTR,
    TB02111_CONTROLESLA,
    TB02112_HORAS,
    TB02112_TIPOHORAS,
    TB02112_TIPOSLA,
    TB02112_CODSITE,
    'A'
) AS FT02020

OUTER APPLY (
    SELECT MAX(ANT.TB02115_DATA) AS DataAnterior
    FROM dbo.TB02115 AS ANT
    WHERE ANT.TB02115_NUMSERIE = dbo.TB02115.TB02115_NUMSERIE
        AND CAST(ANT.TB02115_CODIGO AS int) < CAST(dbo.TB02115.TB02115_CODIGO AS int)
) AS HistoricoOS

OUTER APPLY (
    SELECT CASE
        WHEN FT02020.CALC_PREVISAO < TRY_CAST(
            CONVERT(varchar(10), dbo.TB02122.TB02122_DATAFIM, 23)
            + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS datetime
        )
        AND DATEDIFF(
            HOUR,
            FT02020.CALC_PREVISAO,
            TRY_CAST(
                CONVERT(varchar(10), dbo.TB02122.TB02122_DATAFIM, 23)
                + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS datetime
            )
        ) < 500000
        THEN DATEDIFF(
            SS,
            FT02020.CALC_PREVISAO,
            TRY_CAST(
                CONVERT(varchar(10), dbo.TB02122.TB02122_DATAFIM, 23)
                + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS datetime
            )
        )
        ELSE 0
    END AS SLA_Estourado_Segundos
) AS CalcSLA

OUTER APPLY (
    SELECT
        COALESCE(SUM(
            CASE
                WHEN D.TB02122_DATAFIM IS NOT NULL
                    AND LEN(D.TB02122_HRDESLOC) >= 5
                    AND H.Deslocamento IS NOT NULL
                    THEN DATEPART(HOUR, H.Deslocamento) * 60 + DATEPART(MINUTE, H.Deslocamento)
                ELSE 0
            END
        ), 0) AS MinutosDesloc,
        COALESCE(SUM(
            CASE
                WHEN H.Fim >= H.Inicio THEN DATEDIFF_BIG(MINUTE, H.Inicio, H.Fim)
                ELSE 0
            END
        ), 0) AS MinutosTempo,
        AVG(
            CASE
                WHEN H.Inicio IS NOT NULL AND H.Fim IS NOT NULL
                    THEN CAST(DATEDIFF_BIG(SECOND, H.Inicio, H.Fim) AS decimal(20, 2))
            END
        ) AS SegundosMedios,
        COALESCE(SUM(COALESCE(S.CustoTecnico, 0)), 0) AS CustoTecnico,
        COALESCE(SUM(COALESCE(S.CustoDeslocamento, 0)), 0) AS CustoDeslocamento,
        COALESCE(SUM(COALESCE(P.CustoPecas, 0)), 0) AS CustoPecas
    FROM dbo.TB02122 AS D
    CROSS APPLY (VALUES (
        TRY_CONVERT(time(0), D.TB02122_HRDESLOC),
        TRY_CONVERT(
            datetime2(0),
            CONVERT(varchar(10), D.TB02122_DATAINI, 23) + ' ' + D.TB02122_HORAINI
        ),
        TRY_CONVERT(
            datetime2(0),
            CONVERT(varchar(10), D.TB02122_DATAFIM, 23) + ' ' + D.TB02122_HORAFIM
        )
    )) AS H(Deslocamento, Inicio, Fim)
    OUTER APPLY (
        SELECT
            SUM(CASE
                WHEN TB02125_CODSERV = '0036' THEN COALESCE(TB02125_TOTVALOR, 0)
                ELSE 0
            END) AS CustoTecnico,
            SUM(CASE
                WHEN TB02125_CODSERV = '0003' THEN COALESCE(TB02125_TOTVALOR, 0)
                ELSE 0
            END) AS CustoDeslocamento
        FROM dbo.TB02125
        WHERE TB02125_CODIGO = D.TB02122_CODIGO
    ) AS S
    OUTER APPLY (
        SELECT SUM(
            COALESCE(TB02124_CUSTO, 0) * COALESCE(TB02124_QTPROD, 0)
        ) AS CustoPecas
        FROM dbo.TB02124
        WHERE TB02124_CODIGO = D.TB02122_CODIGO
    ) AS P
    WHERE D.TB02122_NUMOS = dbo.TB02115.TB02115_CODIGO
) AS CalcOS

OUTER APPLY (
    SELECT TOP (1)
        D.TB01048_NOME AS Defeito,
        D.TB01048_CODIGO AS CODIGO
    FROM dbo.TB02116 AS OD
    INNER JOIN dbo.TB01048 AS D
        ON D.TB01048_CODIGO = OD.TB02116_DEFEITO
    WHERE OD.TB02116_CODIGO = dbo.TB02115.TB02115_CODIGO
    ORDER BY OD.TB02116_DEFEITO
) AS CalcDefeito;
