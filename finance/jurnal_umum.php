<?php
include "../config/db.php";

/* =========================
   FILTER
========================= */

$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$klien     = $_GET['klien'] ?? '';

$where = "WHERE 1=1";

if($tgl_awal != '' && $tgl_akhir != ''){
    $where .= " 
    AND j.tanggal 
    BETWEEN '$tgl_awal' 
    AND '$tgl_akhir'
    ";
}

if($klien != ''){
    $where .= "
    AND (
        r1.nama_klien = '$klien'
        OR
        r2.nama_klien = '$klien'
    )
    ";
}

// =========================
// EXPORT PDF
// =========================
if(isset($_GET['export']) && $_GET['export'] == 'pdf'){
?>
<script>
window.onload = function(){

    const style = document.createElement('style');
    style.innerHTML = `
        @page{
            size: landscape;
        }
    `;
    document.head.appendChild(style);

    window.print();
}
</script>
<?php
}

/* =========================
   QUERY JURNAL
========================= */

$q = mysqli_query($conn, "

SELECT
 j.*,
 a.kode_akun,
 a.nama_akun,

 COALESCE(r1.nama_klien, r2.nama_klien) AS nama_klien,

 COALESCE(
     r1.jenis_penugasan,
     r2.jenis_penugasan
 ) AS jenis_penugasan

FROM jurnal_umum j

LEFT JOIN akun a 
ON j.akun_id = a.id

/* =========================
   DARI PENCAIRAN
========================= */

LEFT JOIN pencairan p 
ON j.pencairan_id = p.id

LEFT JOIN rab r1 
ON p.rab_id = r1.id

/* =========================
   DARI LPJ
========================= */

LEFT JOIN lpj l 
ON j.ref_no = l.no_lpj

LEFT JOIN rab r2 
ON l.rab_id = r2.id

$where

ORDER BY 
j.created_at ASC,

CASE
    WHEN j.ref_no LIKE 'PAY/%' THEN 1
    WHEN j.ref_no LIKE 'LPJ/%' THEN 2
    ELSE 3
END,

j.ref_no ASC,

CASE 
    WHEN j.kredit > 0 THEN 2
    ELSE 1
END,

j.id ASC

");
?>

<!DOCTYPE html>
<html>

<head>

<title>Jurnal Umum</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f4f4;
}

/* =========================
   CONTENT
========================= */

.content{
    width:95%;
    max-width:1300px;
    margin:30px auto;
}

.filter-box{
    background:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

.table-container{
    background:white;
    padding:15px;
    border-radius:10px;
}

@media screen{
    .content{
        margin-left:250px;
    }
}

/* =========================
   TITLE
========================= */

.page-title{
    margin-bottom:20px;
}

/* =========================
   FILTER
========================= */

.filter-box{
    background:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;

    display:flex;
    gap:15px;
    flex-wrap:wrap;
    align-items:end;
}

.filter-group{
    display:flex;
    flex-direction:column;
    gap:5px;
}

.filter-group label{
    font-size:13px;
    font-weight:bold;
}

.filter-group input,
.filter-group select{
    padding:8px 10px;
    min-width:180px;
    border:1px solid #ccc;
    border-radius:5px;
}

/* BUTTON */

.btn-filter{
    background:#b8860b;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
}

.btn-filter:hover{
    background:#966d08;
}

/* =========================
   TABLE
========================= */

.table-box{
    background:white;
    border-radius:10px;
    overflow:hidden;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th, td{
    padding:10px;
    border:1px solid #ccc;
}

th{
    background:#b8860b;
    color:white;
}

/* ALIGN */

.text-right{
    text-align:right;
}

.indent{
    padding-left:30px;
}

/* EMPTY */

.empty{
    background:white;
    padding:15px;
    border-radius:10px;
}

td:first-child,
th:first-child{
    text-align:center;
}

td:nth-child(2),
th:nth-child(2){
    text-align:center;
}

td:nth-child(3),
th:nth-child(3){
    text-align:center;
}

td:nth-child(4),
th:nth-child(4){
    text-align:center;
}

td:nth-child(5),
th:nth-child(5){
    text-align:center;
}

/* EXPORT BUTTON */
.btn-excel,
.btn-pdf{
    padding:10px 18px;
    border-radius:5px;
    color:white;
    text-decoration:none;
    font-weight:bold;
    display:inline-block;
}

.btn-excel{
    background:#198754;
}

.btn-excel:hover{
    background:#157347;
}

.btn-pdf{
    background:#dc3545;
}

.btn-pdf:hover{
    background:#bb2d3b;
}

.action-bar{
    display:flex;
    align-items:center;
    width:100%;
    margin-top:10px;
}

.export-group{
    margin-left:auto;
    display:flex;
    gap:12px;
}

.btn-excel,
.btn-pdf{
    padding:10px 16px;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
    font-size:13px;
}

.btn-excel{
    background:#198754;
}

.btn-pdf{
    background:#dc3545;
}

@media print{

    body{
        background:white;
        padding:0;
        margin:0;
    }

    .sidebar,
    .filter-box,
    .export-group,
    .topbar,
    button{
        display:none !important;
    }

    #export-area{
        width:100%;
    }

    table{
        width:100%;
        font-size:12px;
    }

    th, td{
        padding:6px;
    }

}

.table-scroll{
    max-height:500px;
    overflow-y:auto;
    border-radius:10px;
}

.table-scroll table{
    width:100%;
    border-collapse:collapse;
}

.table-scroll thead th{
    position:sticky;
    top:0;
    z-index:100;
    background:#b8860b;
    color:white;
}

</style>

</head>

<body>

<?php if(!isset($_GET['export'])){ ?>
<?php } ?>

<div class="content">

    <h2 class="page-title">
        Jurnal Umum
    </h2>

    <!-- =========================
         FILTER
    ========================= -->
<div class="filter-box">
    <form method="GET">

        <div class="filter-box">

            <div class="filter-group">

                <label>Tanggal Awal</label>

                <input 
                    type="date"
                    name="tgl_awal"
                    value="<?= $tgl_awal ?>"
                >

            </div>

            <div class="filter-group">

                <label>Tanggal Akhir</label>

                <input 
                    type="date"
                    name="tgl_akhir"
                    value="<?= $tgl_akhir ?>"
                >

            </div>

            <div class="filter-group">

                <label>Nama Klien</label>

                <select name="klien">

                    <option value="">
                        -- Semua Klien --
                    </option>

                    <?php
                    $q_klien = mysqli_query($conn,"
                        SELECT DISTINCT nama_klien
                        FROM rab
                        ORDER BY nama_klien ASC
                    ");

                    while($k = mysqli_fetch_assoc($q_klien)){
                    ?>

                    <option 
                        value="<?= $k['nama_klien'] ?>"
                        <?= $klien == $k['nama_klien'] ? 'selected' : '' ?>
                    >

                        <?= $k['nama_klien'] ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <button 
                type="submit"
                class="btn-filter"
            >
                Filter
            </button>

            <a href="javascript:void(0)" onclick="exportTableToExcel()" class="btn-excel">
    Export Excel
</a>

<a href="?export=pdf" class="btn-pdf">
    Export PDF
</a>

        </div>

    </form>
</div>

    <!-- =========================
         TABLE
    ========================= -->

    <?php if(mysqli_num_rows($q) == 0){ ?>

        <div class="empty">
            Belum ada data jurnal.
        </div>

    <?php } else { ?>

    <div class="table-box">
    <div class="table-container">
    <div id="export-area">


        <table>

            <tr>

                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Klien</th>
                <th>Jenis Penugasan</th>
                <th>No Akun</th>
                <th>Keterangan</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>

            </tr>

            <?php

            $no = 1;
            $current_group = '';

            while($d = mysqli_fetch_assoc($q)){

                $group_key = $d['tanggal'] . '_' . $d['ref_no'];

                $isNew = ($current_group != $group_key);

                $current_group = $group_key;

                $nama_klien = !empty($d['nama_klien']) 
                    ? $d['nama_klien'] 
                    : '-';
            ?>

            <tr>

                <td>
                    <?= $isNew ? $no++ : '' ?>
                </td>

                <td>
                    <?= $isNew ? date('d/m/Y', strtotime($d['tanggal'])) : '' ?>
                </td>

                <td>
                    <?= $isNew ? $nama_klien : '' ?>
                </td>
                
                <td>
                    <?= $isNew ? $d['jenis_penugasan'] : '' ?>
                </td>

                <td>
                    <?= $d['kode_akun'] ?>
                </td>

                <td class="<?= $d['kredit'] > 0 ? 'indent' : '' ?>">

                    <?= $d['nama_akun'] ?>

                </td>

                <td class="text-right">

                    <?= $d['debit'] > 0 
                        ? number_format($d['debit'],0,',','.') 
                        : '' ?>

                </td>

                <td class="text-right">

                    <?= $d['kredit'] > 0 
                        ? number_format($d['kredit'],0,',','.') 
                        : '' ?>

                </td>

            </tr>

            <?php } ?>

            <tr style="background:#f2f2f2; font-weight:bold;">

    <td colspan="6" style="text-align:center;">
        TOTAL
    </td>

    <td class="text-right">
        <?=
        number_format(
            mysqli_fetch_assoc(
                mysqli_query($conn,"
                    SELECT SUM(debit) as total_debit
                    FROM jurnal_umum
                ")
            )['total_debit']
        ,0,',','.')
        ?>
    </td>

    <td class="text-right">
        <?=
        number_format(
            mysqli_fetch_assoc(
                mysqli_query($conn,"
                    SELECT SUM(kredit) as total_kredit
                    FROM jurnal_umum
                ")
            )['total_kredit']
        ,0,',','.')
        ?>
    </td>

</tr>

        </table>
</div>

        </div>
    </div>

    <?php } ?>

</div>

<script>
function exportTableToExcel(){

    let table = document.getElementById("export-area").outerHTML;

    let html = `
    <html>
    <head>
    <meta charset="UTF-8">
    <style>
        table{
            border-collapse:collapse;
            width:100%;
        }

        th, td{
            border:1px solid #ccc;
            padding:10px;
            text-align:center;
        }

        th{
            background:#b8860b;
            color:white;
        }

        .text-right{
            text-align:right;
        }
    </style>
    </head>
    <body>
    ${table}
    </body>
    </html>
    `;

    let blob = new Blob([html], {
        type: "application/vnd.ms-excel"
    });

    let link = document.createElement("a");

    link.href = URL.createObjectURL(blob);

    link.download = "jurnal_umum.xls";

    link.click();
}
</script>

</body>
</html>