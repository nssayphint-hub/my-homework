<?php

require_once 'config.php';

function safe($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// รับ VIN
$vin = strtoupper(trim($_GET['vin'] ?? ''));

$errorMessage = '';
$car = null;


// ตรวจสอบ VIN
if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {

    $errorMessage = 'VIN ไม่ถูกต้อง';

} else {

    // Auto.dev API
    $apiUrl =
        'https://api.auto.dev/listings/'
        . rawurlencode($vin);


    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $apiUrl,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_TIMEOUT => 15,

        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . AUTO_DEV_API_KEY,
            'Accept: application/json'
        ]

    ]);


    $response = curl_exec($ch);

    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );


    if ($response === false) {

        $errorMessage =
            'เกิดข้อผิดพลาด: '
            . curl_error($ch);

    }

    curl_close($ch);


    if (
        $response !== false &&
        $httpCode === 200
    ) {

        $result =
            json_decode(
                $response,
                true
            );

        $car =
            $result['data']
            ?? null;

    } elseif ($errorMessage === '') {

        $errorMessage =
            'ไม่พบข้อมูลรถคันนี้';

    }

}


// ข้อมูลรถ
$vehicle =
    $car['vehicle']
    ?? [];

$retail =
    $car['retailListing']
    ?? [];


$year =
    $vehicle['year']
    ?? '-';

$make =
    $vehicle['make']
    ?? '-';

$model =
    $vehicle['model']
    ?? '-';

$trim =
    $vehicle['trim']
    ?? '-';

$engine =
    $vehicle['engine']
    ?? '-';

$fuel =
    $vehicle['fuel']
    ?? '-';

$transmission =
    $vehicle['transmission']
    ?? '-';

$drivetrain =
    $vehicle['drivetrain']
    ?? '-';

$doors =
    $vehicle['doors']
    ?? '-';

$seats =
    $vehicle['seats']
    ?? '-';


$price =
    $retail['price']
    ?? null;

$miles =
    $retail['miles']
    ?? null;

$image =
    $retail['primaryImage']
    ?? '';

$dealer =
    $retail['dealer']
    ?? '-';

$city =
    $retail['city']
    ?? '';

$state =
    $retail['state']
    ?? '';

$zip =
    $retail['zip']
    ?? '';

?>

<!DOCTYPE html>

<html lang="th">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    รายละเอียดรถ | Car Explorer
</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background:
        linear-gradient(
            135deg,
            #111827,
            #1f2937
        );

    color: white;

    min-height: 100vh;

    padding: 30px;
}


.container {

    max-width: 1000px;

    margin: auto;
}


.back-btn {

    display: inline-block;

    margin-bottom: 20px;

    padding: 10px 18px;

    background: #374151;

    color: white;

    text-decoration: none;

    border-radius: 8px;
}


.card {

    background: white;

    color: #111827;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,.4);
}


.car-image {

    width: 100%;

    max-height: 550px;

    object-fit: cover;

    display: block;
}


.no-image {

    height: 350px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 80px;

    background: #e5e7eb;
}


.content {

    padding: 30px;
}


h1 {

    margin-top: 0;

    font-size: 36px;
}


.vin {

    color: #6b7280;

    margin-bottom: 20px;
}


.price {

    color: #dc2626;

    font-size: 34px;

    font-weight: bold;

    margin-bottom: 25px;
}


.grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(200px, 1fr)
        );

    gap: 15px;
}


.item {

    background: #f3f4f6;

    padding: 18px;

    border-radius: 12px;
}


.label {

    font-size: 13px;

    color: #6b7280;

    margin-bottom: 6px;
}


.value {

    font-size: 18px;

    font-weight: bold;
}


.dealer {

    margin-top: 30px;

    padding-top: 20px;

    border-top:
        1px solid
        #e5e7eb;
}


.error {

    background: #fee2e2;

    color: #b91c1c;

    padding: 20px;

    border-radius: 10px;

    text-align: center;
}


footer {

    text-align: center;

    margin-top: 30px;

    color: #9ca3af;
}

</style>

</head>


<body>

<div class="container">


<a
    href="index.php"
    class="back-btn"
>
    ← กลับหน้าค้นหารถ
</a>


<?php if ($errorMessage !== ''): ?>

    <div class="error">

        ❌
        <?php
        echo safe(
            $errorMessage
        );
        ?>

    </div>

<?php endif; ?>


<?php if ($car): ?>


<div class="card">


<?php if ($image !== ''): ?>

    <img
        src="<?php echo safe($image); ?>"
        class="car-image"
        alt="Car Image"
    >

<?php else: ?>

    <div class="no-image">
        🚘
    </div>

<?php endif; ?>


<div class="content">


<h1>

    <?php echo safe($year); ?>

    <?php echo safe($make); ?>

    <?php echo safe($model); ?>

    <?php echo safe($trim); ?>

</h1>


<div class="vin">

    VIN:
    <?php echo safe($vin); ?>

</div>


<div class="price">

<?php if (
    $price !== null &&
    $price > 0
): ?>

    $<?php
        echo number_format(
            $price
        );
    ?>

<?php else: ?>

    ไม่ระบุราคา

<?php endif; ?>

</div>


<div class="grid">


<div class="item">

    <div class="label">
        ⚙️ เครื่องยนต์
    </div>

    <div class="value">
        <?php echo safe($engine); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        ⛽ เชื้อเพลิง
    </div>

    <div class="value">
        <?php echo safe($fuel); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        🔄 เกียร์
    </div>

    <div class="value">
        <?php echo safe($transmission); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        🚙 ระบบขับเคลื่อน
    </div>

    <div class="value">
        <?php echo safe($drivetrain); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        🚪 จำนวนประตู
    </div>

    <div class="value">
        <?php echo safe($doors); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        💺 จำนวนที่นั่ง
    </div>

    <div class="value">
        <?php echo safe($seats); ?>
    </div>

</div>


<div class="item">

    <div class="label">
        🛣 เลขไมล์
    </div>

    <div class="value">

        <?php

        echo $miles !== null
            ? number_format($miles)
                . ' miles'
            : '-';

        ?>

    </div>

</div>


<div class="item">

    <div class="label">
        📅 ปี
    </div>

    <div class="value">
        <?php echo safe($year); ?>
    </div>

</div>


</div>


<div class="dealer">

    <h3>
        🏢 ข้อมูลตัวแทนจำหน่าย
    </h3>

    <p>

        <strong>ชื่อ:</strong>

        <?php
        echo safe($dealer);
        ?>

    </p>


    <p>

        <strong>สถานที่:</strong>

        <?php

        echo safe(
            trim(
                $city .
                ', ' .
                $state .
                ' ' .
                $zip
            )
        );

        ?>

    </p>

</div>


</div>

</div>


<?php endif; ?>


<footer>

    Data provided by Auto.dev

</footer>


</div>

</body>

</html>