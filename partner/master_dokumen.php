<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'partner') {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

/* =========================
   QUERY MASTER DOKUMEN
========================= */

$query = mysqli_query($conn, "

SELECT

    rab.id,
    rab.no_rab,
    rab.nama_klien,
    rab.jenis_penugasan,
    rab.periode_awal,
    rab.periode_akhir,

    pencairan.id AS pencairan_id,

    lpj.id AS lpj_id

FROM rab

LEFT JOIN pencairan
ON rab.id = pencairan.rab_id

LEFT JOIN lpj
ON rab.id = lpj.rab_id

WHERE pencairan.id IS NOT NULL

ORDER BY rab.id DESC

");

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Master Dokumen Penugasan
    </title>

    <style>

        body{
            margin:0;
            padding:0;
            background:#f4f4f4;
            font-family:Arial;
            display:flex;
        }

        .content{
    width:95%;
    margin:auto;
    padding:20px;
}

        .container{
    width:95%;
    margin:auto;
}

        h1{
            margin-top:0;
            margin-bottom:30px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#c79a00;
            color:white;
            border:1px solid #444;
            padding:12px;
            text-align:center;
        }

        td{
            border:1px solid #444;
            padding:10px;
            text-align:center;
        }

        .text-left{
            text-align:left;
        }

        .btn-detail{
            display:inline-block;
            padding:7px 14px;
            border-radius:6px;
            background:#0d6efd;
            color:white;
            text-decoration:none;
            font-size:13px;
            font-weight:bold;
        }

        .btn-detail:hover{
            background:#0b5ed7;
        }

        .belum{
            color:red;
            font-weight:bold;
            font-size:13px;
        }

        @media print{

            .content{
                margin-left:0;
                width:100%;
                padding:0;
            }

            .container{
                box-shadow:none;
                border:none;
            }

        }

    </style>

</head>

<body>

<!-- CONTENT -->

<div class="content">

    <div class="container">

        <h1>
            Master Dokumen Penugasan
        </h1>

        <table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>
                        Nama Klien
                    </th>

                    <th>
                        Jenis Penugasan
                    </th>

                    <th>
                        Periode Penugasan
                    </th>

                    <th>
                        RAB
                    </th>

                    <th>
                        Pencairan Dana
                    </th>

                    <th>
                        LPJ
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php

            $no = 1;

            while($d = mysqli_fetch_assoc($query)) :

                $periode_awal =
                    date(
                        'd-m-Y',
                        strtotime($d['periode_awal'])
                    );

                $periode_akhir =
                    date(
                        'd-m-Y',
                        strtotime($d['periode_akhir'])
                    );

            ?>

                <tr>

                    <!-- NO -->

                    <td>
                        <?= $no++ ?>
                    </td>

                    <!-- NAMA KLIEN -->

                    <td class="text-left">
                        <?= $d['nama_klien'] ?>
                    </td>

                    <!-- JENIS PENUGASAN -->

                    <td class="text-left">
                        <?= $d['jenis_penugasan'] ?>
                    </td>

                    <!-- PERIODE -->

                    <td>

                        <?= $periode_awal ?>
                        s/d
                        <?= $periode_akhir ?>

                    </td>

                    <!-- RAB -->

                    <td>

                        <a
                            class="btn-detail"

                            href="../admin/rab_detail.php?id=<?= $d['id'] ?>"
                        >

                            Detail

                        </a>

                    </td>

                    <!-- PENCAIRAN -->

                    <td>

                        <?php if($d['pencairan_id']){ ?>

                            <a
                                class="btn-detail"

                                href="../finance/pencairan_detail.php?id=<?= $d['id'] ?>"
                            >

                                Detail

                            </a>

                        <?php } else { ?>

                            <span class="belum">
                                Belum Ada
                            </span>

                        <?php } ?>

                    </td>

                    <!-- LPJ -->
<td>

<?php if($d['lpj_id']){ ?>

    <a 
        class="btn-detail"
        href="../finance/lpj_detail.php?id=<?= $d['lpj_id'] ?>"
    >
        Detail
    </a>

<?php } else { ?>

    <span class="belum">
        Belum Ada
    </span>

<?php } ?>

</td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>