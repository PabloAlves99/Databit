SELECT
    dbo.TB02115.TB02115_CODIGO os,
    dbo.TB02115.TB02115_CONTRATO contrato,
    dbo.TB02115.TB02115_CODCLI codcli,
    dbo.TB02115.TB02115_PRODUTO codprod,
    dbo.TB01010.TB01010_NOME nomeprod,
    dbo.TB02115.TB02115_NUMSERIE numserie,
    dbo.TB02115.TB02115_DATA [data],
    dbo.TB02115.TB02115_ATENDENTE atendente,
    TB02115_CODEMP codemp,
    TB02115_SITUACAO situacao,
    TB02115_NOME nome,
    TB02115_PREVENTIVA preventiva,
    TB02115_DTFECHA dtfecha,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02112.TB02112_CIDADE
        ELSE dbo.TB00012.TB00012_CIDADE
    END AS cidade,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02112.TB02112_TIPOINTERV
        ELSE 1
    END AS tipointerv ,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02112.TB02112_CODTEC
        ELSE '0000'
    END AS codtec,

    CASE
        WHEN dbo.TB02115.TB02115_ORIGEM = 'C' THEN dbo.TB02112.TB02112_PREST
        ELSE '0000'
    END AS prest,

    CalcDefeito.Defeito AS Defeito,
    TB01055_NOME CondIntervencao,

    CalcSLA.SLA_Estourado_Segundos,
    CASE
        WHEN LEN(
            CAST(
                CalcSLA.SLA_Estourado_Segundos / 3600 AS VARCHAR(20)
            )
        ) = 1 THEN '0' + CAST(
            CalcSLA.SLA_Estourado_Segundos / 3600 AS VARCHAR(20)
        )
        ELSE CAST(
            CalcSLA.SLA_Estourado_Segundos / 3600 AS VARCHAR(20)
        )
    END + ':' + RIGHT(
        '00' + CAST(
            (CalcSLA.SLA_Estourado_Segundos % 3600) / 60 AS VARCHAR(2)
        ),
        2
    ) + ':' + RIGHT(
        '00' + CAST(
            CalcSLA.SLA_Estourado_Segundos % 60 AS VARCHAR(2)
        ),
        2
    ) AS TimeSLANegative,
    CASE
        WHEN CALC_PREVISAO < CAST(
            CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 23) + ' ' + dbo.TB02122.TB02122_HORAFIM + ':00' AS DATETIME
        ) THEN 1
        ELSE 0
    END AS SLAExcedido,
    CASE
        WHEN DATEDIFF(
            DAY,
            ISNULL(HistoricoOS.DataAnterior, '19900101'),
            dbo.TB02115.TB02115_DATA
        ) <= 30 THEN 1
        ELSE 0
    END AS ReincidenciaINT,
    CONCAT(
        CASE
            WHEN CalcOS.MinutosTempo / 60 < 10 THEN '0'
            ELSE ''
        END,
        CalcOS.MinutosTempo / 60,
        ':',
        RIGHT(
            '00' + CAST(CalcOS.MinutosTempo % 60 AS varchar(2)),
            2
        )
    ) AS Monteiro,
    CONCAT(
        CASE
            WHEN CalcOS.MinutosTempo / 60 < 10 THEN '0'
            ELSE ''
        END,
        CalcOS.MinutosTempo / 60,
        ':',
        RIGHT(
            '00' + CAST(CalcOS.MinutosTempo % 60 AS varchar(2)),
            2
        )
    ) AS MonteiroDesloc,
    dbo.TB02122.TB02122_KMINICIAL KMInicial,
    dbo.TB02122.TB02122_KMFINAL KMFinal,
    dbo.TB02122.TB02122_KMFINAL - dbo.TB02122.TB02122_KMINICIAL KMSaldo,
    CAST(dbo.TB02115.TB02115_DATA AS date) AS DataTrabalho,
    dbo.TB01024.TB01024_NOME AS TecnicoDaysWorked,
    CalcOS.CustoTecnico AS [Custo técnico],
    CalcOS.CustoDeslocamento AS [Custo Deslocamento],
    CalcOS.CustoPecas AS [Custo peças],
    CONVERT(
        varchar(5),
        DATEADD(
            SECOND,
            CONVERT(
                int,
                CONVERT(bigint, ROUND(CalcOS.SegundosMedios, 0)) % 86400
            ),
            '19000101'
        ),
        108
    ) AS TempoMedioAtendSLA
FROM
    TB02115
    LEFT JOIN dbo.TB02112 ON dbo.TB02115.TB02115_CONTRATO = dbo.TB02112.TB02112_CODIGO
        AND dbo.TB02115.TB02115_PRODUTO = dbo.TB02112.TB02112_PRODUTO
        AND dbo.TB02115.TB02115_NUMSERIE = dbo.TB02112.TB02112_NUMSERIE
    LEFT JOIN dbo.TB01008 ON dbo.TB02115.TB02115_CODCLI = dbo.TB01008.TB01008_CODIGO
    LEFT JOIN dbo.TB01010 ON dbo.TB02115.TB02115_PRODUTO = dbo.TB01010.TB01010_CODIGO
    LEFT JOIN dbo.TB01024 ON dbo.TB02115.TB02115_CODTEC = dbo.TB01024.TB01024_CODIGO
    LEFT JOIN dbo.TB01024 as TB01024_2 ON dbo.TB02112.TB02112_CODTEC = TB01024_2.TB01024_CODIGO
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
                    ) as ORDEM
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
    ) TB02018 ON TB02018.TB02018_OS = TB02115_CODIGO
    LEFT JOIN --S82748 Rodrigo - Criação da junção com TB02018
    (
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
                    ) as ORDEM
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
    ) TB02021 ON TB02021.TB02021_OS = TB02115_CODIGO
    OUTER APPLY --S82748 Rodrigo - Criação da junção com TB02021
    dbo.FT02020(
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
    ) as FT02020 --S82748 Rodrigo - Criação da junção com FT02020

    OUTER APPLY (
        SELECT
            MAX(ANT.TB02115_DATA) AS DataAnterior
        FROM
            dbo.TB02115 AS ANT
        WHERE
            ANT.TB02115_NUMSERIE = dbo.TB02115.TB02115_NUMSERIE
            AND CAST(ANT.TB02115_CODIGO AS int) < CAST(dbo.TB02115.TB02115_CODIGO AS int)
    ) AS HistoricoOS

    OUTER APPLY (
        SELECT
            CASE
                WHEN FT02020.CALC_PREVISAO < TRY_CAST(
                    CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 23) + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS DATETIME
                )
                AND DATEDIFF(
                    HOUR,
                    FT02020.CALC_PREVISAO,
                    TRY_CAST(
                        CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 23) + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS DATETIME
                    )
                ) < 500000 -- Evita overflow se a data estiver corrompida
                THEN DATEDIFF(
                    SS,
                    FT02020.CALC_PREVISAO,
                    TRY_CAST(
                        CONVERT(VARCHAR(10), dbo.TB02122.TB02122_DATAFIM, 23) + ' ' + ISNULL(dbo.TB02122.TB02122_HORAFIM, '00:00') + ':00' AS DATETIME
                    )
                )
                ELSE 0
            END AS SLA_Estourado_Segundos
    ) AS CalcSLA

    OUTER APPLY (
        SELECT
            COALESCE(
                SUM(
                    CASE
                        WHEN D.TB02122_DATAFIM IS NOT NULL
                        AND LEN(D.TB02122_HRDESLOC) >= 5
                        AND H.Deslocamento IS NOT NULL THEN DATEPART(HOUR, H.Deslocamento) * 60 + DATEPART(MINUTE, H.Deslocamento)
                        ELSE 0
                    END
                ),
                0
            ) AS MinutosDesloc,
            COALESCE(
                SUM(
                    CASE
                        WHEN H.Fim >= H.Inicio THEN DATEDIFF_BIG(MINUTE, H.Inicio, H.Fim)
                        ELSE 0
                    END
                ),
                0
            ) AS MinutosTempo,
            AVG(
                CASE
                    WHEN H.Inicio IS NOT NULL
                    AND H.Fim IS NOT NULL THEN CAST(
                        DATEDIFF_BIG(SECOND, H.Inicio, H.Fim) AS decimal(20, 2)
                    )
                END
            ) AS SegundosMedios,
            COALESCE(SUM(COALESCE(S.CustoTecnico, 0)), 0) AS CustoTecnico,
            COALESCE(SUM(COALESCE(S.CustoDeslocamento, 0)), 0) AS CustoDeslocamento,
            COALESCE(SUM(COALESCE(P.CustoPecas, 0)), 0) AS CustoPecas
        FROM
            dbo.TB02122 AS D
            CROSS APPLY (
                VALUES
                    (
                        TRY_CONVERT(time(0), D.TB02122_HRDESLOC),
                        TRY_CONVERT(
                            datetime2(0),
                            CONVERT(varchar(10), D.TB02122_DATAINI, 23) + ' ' + D.TB02122_HORAINI
                        ),
                        TRY_CONVERT(
                            datetime2(0),
                            CONVERT(varchar(10), D.TB02122_DATAFIM, 23) + ' ' + D.TB02122_HORAFIM
                        )
                    )
            ) AS H(Deslocamento, Inicio, Fim)
            OUTER APPLY (
                SELECT
                    SUM(
                        CASE
                            WHEN TB02125_CODSERV = '0036' THEN COALESCE(TB02125_TOTVALOR, 0)
                            ELSE 0
                        END
                    ) AS CustoTecnico,
                    SUM(
                        CASE
                            WHEN TB02125_CODSERV = '0003' THEN COALESCE(TB02125_TOTVALOR, 0)
                            ELSE 0
                        END
                    ) AS CustoDeslocamento
                FROM
                    dbo.TB02125
                WHERE
                    TB02125_CODIGO = D.TB02122_CODIGO
            ) AS S
            OUTER APPLY (
                SELECT
                    SUM(
                        COALESCE(TB02124_CUSTO, 0) * COALESCE(TB02124_QTPROD, 0)
                    ) AS CustoPecas
                FROM
                    dbo.TB02124
                WHERE
                    TB02124_CODIGO = D.TB02122_CODIGO
            ) AS P
        WHERE
            D.TB02122_NUMOS = dbo.TB02115.TB02115_CODIGO
    ) AS CalcOS

    OUTER APPLY (
        SELECT TOP (1) D.TB01048_NOME AS Defeito
        FROM dbo.TB02116 AS OD
        INNER JOIN dbo.TB01048 AS D
            ON D.TB01048_CODIGO = OD.TB02116_DEFEITO
        WHERE OD.TB02116_CODIGO = dbo.TB02115.TB02115_CODIGO
        ORDER BY OD.TB02116_DEFEITO
    ) AS CalcDefeito