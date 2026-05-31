<?php
session_start();
include "../config/db.php";

/* =========================
   FILTER
========================= */

$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$klien     = $_GET['klien'] ?? '';

$where = [];

if($tgl_awal != '' && $tgl_akhir != ''){
    $where[] = "
        DATE(
            COALESCE(
                p.created_at,
                j.tanggal
            )
        ) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    ";
}

if($klien != ''){
    $where[] = "
        COALESCE(
            r1.nama_klien,
            r2.nama_klien
        ) = '$klien'
    ";
}

$where_sql = '';

if(count($where) > 0){
    $where_sql = "WHERE ".implode(" AND ", $where);
}

/* =========================
   DROPDOWN KLIEN
========================= */

$q_klien = mysqli_query($conn, "
    SELECT DISTINCT nama_klien
    FROM rab
    ORDER BY nama_klien ASC
");

/* =========================
   QUERY LAPORAN
========================= */

$query = mysqli_query($conn, "

SELECT

    COALESCE(r1.id, r2.id) AS rab_id,

    COALESCE(
        r1.nama_klien,
        r2.nama_klien
    ) AS nama_klien,

    COALESCE(
        r1.jenis_penugasan,
        r2.jenis_penugasan
    ) AS jenis_penugasan,

    MAX(
        CASE
            WHEN a.nama_akun = 'Cash Advance'
            THEN DATE(
                COALESCE(
                    p.created_at,
                    j.tanggal
                )
            )
        END
    ) AS tgl_anggaran,

    MAX(
        CASE
            WHEN a.nama_akun = 'Cash Advance'
            THEN
                CASE
                    WHEN j.debit > 0
                    THEN j.debit
                    ELSE j.kredit
                END
        END
    ) AS nominal_anggaran,

    MAX(
        CASE
            WHEN a.kode_akun IN (
                '5-101',
                '5-102',
                '5-103',
                '5-104'
            )
            THEN DATE(j.tanggal)
        END
    ) AS tgl_realisasi,

    SUM(
        CASE

            WHEN a.kode_akun IN (
                '5-101',
                '5-102',
                '5-103',
                '5-104'
            )

            THEN j.debit

            ELSE 0

        END
    ) AS nominal_realisasi

FROM jurnal_umum j

LEFT JOIN akun a
ON j.akun_id = a.id

LEFT JOIN pencairan p
ON j.pencairan_id = p.id

LEFT JOIN rab r1
ON p.rab_id = r1.id

LEFT JOIN lpj l
ON j.ref_no = l.no_lpj

LEFT JOIN rab r2
ON l.rab_id = r2.id

$where_sql

GROUP BY
COALESCE(r1.id, r2.id)

ORDER BY
tgl_anggaran DESC

");

?>

<!DOCTYPE html>
<html>
<head>

<title>Data Laporan</title>

<style>

body{
    font-family:Arial, sans-serif;
    background:#f4f4f4;
    margin:0;
    padding:30px;
}

.container{
    max-width:1400px;
    margin:auto;
    background:#fff;
    padding:35px;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.judul-laporan{
    text-align:center;
    margin-bottom:30px;
}

.judul-laporan h2{
    margin:0;
    line-height:1.4;
    font-size:32px;
    font-weight:bold;
}

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:25px;
    flex-wrap:wrap;
    gap:15px;
}

.export-group{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn-export{
    background:#c79a06;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:6px;
    font-size:14px;
    font-weight:bold;
    cursor:pointer;
}

.filter-box{
    display:flex;
    gap:10px;
    align-items:center;
    flex-wrap:wrap;
}

.filter-box input,
.filter-box select{
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
}

.filter-box button{
    background:#c79a06;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

table th,
table td{
    border:1px solid #000;
    padding:8px;
    text-align:center;
    font-size:14px;
}

table th{
    background:#c79a06;
    color:white;
    font-weight:bold;
}

.belum{
    color:red;
    font-style:italic;
}

/* =========================
   PRINT PDF
========================= */

@media print{

    body{
        background:white;
        padding:0;
        margin:0;
    }

    .top-bar{
        display:none;
    }

    .container{
        box-shadow:none;
        border:none;
        max-width:100%;
        padding:10px;
    }

    .judul-laporan h2{
        font-size:20px;
    }

    table{
        width:100%;
        table-layout:fixed;
    }

    table th,
    table td{
        font-size:10px;
        padding:4px;
        word-wrap:break-word;
    }

    @page{
        size:landscape;
        margin:10mm;
    }

}

</style>

</head>

<body>

<div class="container">

    <!-- JUDUL -->

    <div class="judul-laporan">

        <h2>
            Laporan Rekapitulasi Penggunaan
        </h2>

        <h2>
            Dana Cash Advance
        </h2>

        <h2>
            KAP PQR
        </h2>

    </div>

    <!-- FILTER + EXPORT -->

    <div class="top-bar">

        <!-- EXPORT -->

        <div class="export-group">

            <button
                class="btn-export"
                onclick="exportExcel()"
            >
                Export Excel
            </button>

            <button
                class="btn-export"
                onclick="exportCSV()"
            >
                Export CSV
            </button>

            <button
                class="btn-export"
                onclick="window.print()"
            >
                Export PDF
            </button>

        </div>

        <!-- FILTER -->

        <form
            method="GET"
            class="filter-box"
        >

            <input
                type="date"
                name="tgl_awal"
                value="<?= $tgl_awal ?>"
            >

            <input
                type="date"
                name="tgl_akhir"
                value="<?= $tgl_akhir ?>"
            >

            <select name="klien">

                <option value="">
                    -- Semua Klien --
                </option>

                <?php
                while($k = mysqli_fetch_assoc($q_klien)) :
                ?>

                <option
                    value="<?= $k['nama_klien'] ?>"
                    <?= $klien == $k['nama_klien'] ? 'selected' : '' ?>
                >

                    <?= $k['nama_klien'] ?>

                </option>

                <?php endwhile; ?>

            </select>

            <button type="submit">
                Filter
            </button>

        </form>

    </div>

    <!-- TABEL -->

    <table id="tableLaporan">

        <thead>

        <tr>

            <th rowspan="2">No</th>

            <th rowspan="2">Nama Klien</th>

            <th rowspan="2">Jenis Penugasan</th>

            <th colspan="2">Anggaran</th>

            <th colspan="2">Realisasi</th>

            <th rowspan="2">Selisih</th>

        </tr>

        <tr>

            <th>Tanggal</th>
            <th>Nominal</th>

            <th>Tanggal</th>
            <th>Nominal</th>

        </tr>

        </thead>

        <tbody>

        <?php
        $no = 1;

        while($d = mysqli_fetch_assoc($query)) :

            $selisih =
                $d['nominal_anggaran']
                -
                $d['nominal_realisasi'];

            $tgl_anggaran = '';

            if($d['tgl_anggaran'] != ''){
                $tgl_anggaran = date(
                    'd-m-Y',
                    strtotime($d['tgl_anggaran'])
                );
            }

            $tgl_realisasi = '';

            if($d['tgl_realisasi'] != ''){
                $tgl_realisasi = date(
                    'd-m-Y',
                    strtotime($d['tgl_realisasi'])
                );
            }

        ?>

        <tr>

            <td>
                <?= $no++ ?>
            </td>

            <td style="text-align:left;">
                <?= $d['nama_klien']; ?>
            </td>

            <td style="text-align:left;">
                <?= $d['jenis_penugasan']; ?>
            </td>

            <!-- ANGGARAN -->

            <td>
                <?= $tgl_anggaran ?>
            </td>

            <td>

                Rp <?= number_format(
                    $d['nominal_anggaran'],
                    0,
                    ',',
                    '.'
                ) ?>

            </td>

            <!-- REALISASI -->

            <td>

                <?php if(
                    $d['nominal_realisasi'] > 0
                ){ ?>

                    <?= $tgl_realisasi ?>

                <?php } else { ?>

                    <span class="belum">
                        Belum ada data
                    </span>

                <?php } ?>

            </td>

            <td>

                <?php if(
                    $d['nominal_realisasi'] > 0
                ){ ?>

                    Rp <?= number_format(
                        $d['nominal_realisasi'],
                        0,
                        ',',
                        '.'
                    ) ?>

                <?php } else { ?>

                    <span class="belum">
                        Belum ada data
                    </span>

                <?php } ?>

            </td>

            <!-- SELISIH -->

            <td>

                <?php if($selisih < 0){ ?>

                    <span style="color:red; font-weight:bold;">

                        (Rp <?= number_format(
                            abs($selisih),
                            0,
                            ',',
                            '.'
                        ) ?>)

                    </span>

                <?php } else { ?>

    <span style="color:green; font-weight:bold;">

        Rp <?= number_format(
            $selisih,
            0,
            ',',
            '.'
        ) ?>

    </span>

<?php } ?>

            </td>

        </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>

<!-- =========================
     EXPORT EXCEL
========================= -->

<script>

function exportExcel(){

    let table =
        document.getElementById("tableLaporan");

    let html =
        table.outerHTML;

    let url =
        'data:application/vnd.ms-excel,' +
        encodeURIComponent(html);

    let a =
        document.createElement('a');

    a.href = url;

    a.download =
        'laporan_cash_advance.xls';

    a.click();
}

/* =========================
   EXPORT CSV
========================= */

function exportCSV(){

    let csv = [];

    let rows =
        document.querySelectorAll("table tr");

    for(let i = 0; i < rows.length; i++){

        let row = [];

        let cols =
            rows[i].querySelectorAll("td, th");

        for(let j = 0; j < cols.length; j++){

            let text =
                cols[j].innerText;

            text =
                text.replace(/Rp/g,'')
                    .replace(/\./g,'')
                    .replace(/\(/g,'-')
                    .replace(/\)/g,'');

            row.push('"' + text + '"');
        }

        csv.push(
            row.join(",")
        );
    }

    let csvFile =
        new Blob(
            [csv.join("\n")],
            {
                type: "text/csv"
            }
        );

    let downloadLink =
        document.createElement("a");

    downloadLink.download =
        "laporan_cash_advance.csv";

    downloadLink.href =
        window.URL.createObjectURL(csvFile);

    downloadLink.style.display =
        "none";

    document.body.appendChild(
        downloadLink
    );

    downloadLink.click();
}

</script>

</body>
</html>