<?php

require_once 'config.php';

/**
 * ฟังก์ชันดึงข้อมูลรถยนต์จาก Auto.dev API
 *
 * @param string $apiKey
 * @param string $make
 * @param string $model
 * @param string $year
 *
 * @return array
 */
function getCarData($apiKey, $make = '', $model = '', $year = '')
{
    // Endpoint Auto.dev
    $apiUrl = 'https://api.auto.dev/listings';

    // Parameters
    $params = [
        'limit' => 20
    ];

    // กรองตามยี่ห้อ
    if ($make !== '') {
        $params['vehicle.make'] = $make;
    }

    // กรองตามรุ่น
    if ($model !== '') {
        $params['vehicle.model'] = $model;
    }

    // กรองตามปี
    if ($year !== '') {
        $params['vehicle.year'] = $year;
    }

    // สร้าง URL
    $apiUrl .= '?' . http_build_query($params);

    // เริ่ม cURL
    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $apiUrl,

        // คืนข้อมูลมาเป็น String
        CURLOPT_RETURNTRANSFER => true,

        // Timeout
        CURLOPT_TIMEOUT => 15,

        // Header สำหรับ API Key
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Accept: application/json'
        ]

    ]);

    // เรียก API
    $response = curl_exec($ch);

    // ตรวจสอบ cURL Error
    if ($response === false) {

        $error = curl_error($ch);

        curl_close($ch);

        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error
        ];
    }

    // HTTP Status Code
    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    // JSON -> PHP Array
    $data = json_decode($response, true);

    // ตรวจสอบ JSON
    if (json_last_error() !== JSON_ERROR_NONE) {

        return [
            'success' => false,
            'message' => 'ข้อมูล JSON จาก API ไม่ถูกต้อง'
        ];
    }

    // ถ้า API ไม่สำเร็จ
    if ($httpCode !== 200) {

        return [
            'success' => false,
            'message' =>
                $data['error']
                ?? $data['message']
                ?? 'ไม่สามารถเรียก Auto.dev API ได้'
        ];
    }

    // ส่งข้อมูลกลับ
    return [
        'success' => true,
        'data' => $data['data'] ?? []
    ];
}


/* ==================================================
   รับค่าจาก Form
================================================== */

$make = trim($_GET['make'] ?? '');
$model = trim($_GET['model'] ?? '');
$year = trim($_GET['year'] ?? '');

$action = $_GET['action'] ?? '';


/* ==================================================
   ตรวจสอบปี
================================================== */

$currentYear = (int)date('Y');

if ($year !== '') {

    if (
        !preg_match('/^\d{4}$/', $year)
        ||
        (int)$year < 1900
        ||
        (int)$year > $currentYear
    ) {

        $year = '';

    }
}


/* ==================================================
   เรียก Auto.dev API
================================================== */

$result = getCarData(
    AUTO_DEV_API_KEY,
    $make,
    $model,
    $year
);


/* ==================================================
   เลือกรถที่จะแสดง
================================================== */

$car = null;

$errorMessage = '';

if ($result['success']) {

    $cars = $result['data'];

    if (count($cars) > 0) {

        // ถ้ากดปุ่มสุ่มรถ
        if ($action === 'random') {

            $randomIndex = array_rand($cars);

            $car = $cars[$randomIndex];

        } else {

            // แสดงรถคันแรก
            $car = $cars[0];

        }

    } else {

        $errorMessage =
            'ไม่พบรถตามเงื่อนไขที่ค้นหา';

    }

} else {

    $errorMessage =
        $result['message'];

}


/* ==================================================
   ป้องกัน XSS
================================================== */

function safe($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* ==================================================
   เตรียมข้อมูลรถ
================================================== */

$vehicle =
    $car['vehicle']
    ?? [];

$retail =
    $car['retailListing']
    ?? [];


// ปี
$yearValue =
    $vehicle['year']
    ?? '-';


// ยี่ห้อ
$makeValue =
    $vehicle['make']
    ?? '-';


// รุ่น
$modelValue =
    $vehicle['model']
    ?? '-';


// รุ่นย่อย
$trimValue =
    $vehicle['trim']
    ?? '';


// เครื่องยนต์
$engine =
    $vehicle['engine']
    ?? '-';


// เชื้อเพลิง
$fuel =
    $vehicle['fuel']
    ?? '-';


// เกียร์
$transmission =
    $vehicle['transmission']
    ?? '-';


// ระบบขับเคลื่อน
$drivetrain =
    $vehicle['drivetrain']
    ?? '-';


// VIN
$vin =
    $vehicle['vin']
    ?? $car['vin']
    ?? '-';


// ราคา
$price =
    $retail['price']
    ?? null;


// เลขไมล์
$miles =
    $retail['miles']
    ?? null;


// รูปรถ
$image =
    $retail['primaryImage']
    ?? '';


// Dealer
$dealer =
    $retail['dealer']
    ?? '-';


// เมือง
$city =
    $retail['city']
    ?? '';


// รัฐ
$state =
    $retail['state']
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
    Car Explorer
</title>


<style>

* {
    box-sizing: border-box;
}


/* ================================
   Body
================================ */

body {

    margin: 0;

    font-family:
        Arial,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #111827,
            #1f2937,
            #111827
        );

    color: white;

    min-height: 100vh;
}


/* ================================
   Header
================================ */

header {

    text-align: center;

    padding:
        50px
        20px
        30px;
}


header h1 {

    font-size: 42px;

    margin:
        0
        0
        10px;
}


header p {

    margin: 0;

    color: #9ca3af;
}


/* ================================
   Search Box
================================ */

.search-box {

    max-width: 900px;

    margin:
        0
        auto
        30px;

    padding: 20px;

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.1
        );

    border-radius: 15px;

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    justify-content: center;
}


.search-box input {

    padding:
        13px
        15px;

    border: none;

    outline: none;

    border-radius: 8px;

    font-size: 16px;

    width: 180px;
}


.search-box button {

    border: none;

    border-radius: 8px;

    padding:
        13px
        20px;

    font-size: 16px;

    cursor: pointer;

    font-weight: bold;

    transition: 0.2s;
}


.search-box button:hover {

    transform:
        translateY(-2px);

    opacity: 0.9;
}


.search-btn {

    background:
        #2563eb;

    color: white;
}


.random-btn {

    background:
        #f97316;

    color: white;
}


/* ================================
   Card
================================ */

.card {

    background:
        white;

    color:
        #111827;

    max-width:
        900px;

    margin:
        20px
        auto
        50px;

    border-radius:
        20px;

    overflow:
        hidden;

    box-shadow:
        0
        20px
        60px
        rgba(
            0,
            0,
            0,
            0.5
        );
}


/* ================================
   Image
================================ */

.car-image {

    width: 100%;

    height:
        450px;

    object-fit:
        cover;

    display:
        block;
}


.no-image {

    height:
        350px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #e5e7eb;

    color:
        #6b7280;

    font-size:
        80px;
}


/* ================================
   Content
================================ */

.car-content {

    padding:
        30px;
}


.car-content h2 {

    font-size:
        32px;

    margin:
        0
        0
        10px;
}


.vin {

    color:
        #6b7280;

    margin-bottom:
        25px;

    word-break:
        break-all;
}


/* ================================
   Price
================================ */

.price {

    font-size:
        34px;

    color:
        #dc2626;

    font-weight:
        bold;

    margin-bottom:
        25px;
}


/* ================================
   Specs
================================ */

.spec-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(
                200px,
                1fr
            )
        );

    gap:
        15px;
}


.spec {

    background:
        #f3f4f6;

    padding:
        15px;

    border-radius:
        10px;
}


.spec-title {

    font-size:
        13px;

    color:
        #6b7280;

    margin-bottom:
        5px;
}


.spec-value {

    font-weight:
        bold;

    font-size:
        17px;

    word-break:
        break-word;
}


/* ================================
   Dealer
================================ */

.dealer {

    margin-top:
        25px;

    padding-top:
        20px;

    border-top:
        1px solid
        #e5e7eb;

    line-height:
        1.8;
}


/* ================================
   Detail Button
================================ */

.view-btn {

    display:
        inline-block;

    margin-top:
        18px;

    background:
        #2563eb;

    color:
        white;

    padding:
        13px
        22px;

    border-radius:
        8px;

    text-decoration:
        none;

    font-weight:
        bold;

    transition:
        0.2s;
}


.view-btn:hover {

    background:
        #1d4ed8;

    transform:
        translateY(-2px);
}


/* ================================
   Error
================================ */

.error {

    max-width:
        700px;

    margin:
        30px
        auto;

    padding:
        20px;

    text-align:
        center;

    background:
        #fee2e2;

    color:
        #b91c1c;

    border-radius:
        10px;
}


/* ================================
   Footer
================================ */

footer {

    text-align:
        center;

    color:
        #9ca3af;

    padding:
        30px;
}


/* ================================
   Mobile
================================ */

@media (
    max-width: 600px
) {

    header h1 {

        font-size:
            30px;

    }


    .search-box {

        margin:
            0
            15px
            30px;

    }


    .search-box input {

        width:
            100%;

    }


    .search-box button {

        width:
            100%;

    }


    .car-image {

        height:
            280px;

    }


    .card {

        margin:
            15px
            15px
            40px;

    }


    .car-content {

        padding:
            20px;

    }


    .car-content h2 {

        font-size:
            25px;

    }

}

</style>

</head>


<body>


<!-- ===============================
     Header
================================ -->

<header>

    <h1>
        🚘 Car Explorer
    </h1>

    <p>
        ค้นหาข้อมูลรถยนต์จาก Auto.dev API
    </p>

</header>


<!-- ===============================
     Search Form
================================ -->

<form
    class="search-box"
    method="GET"
>


    <!-- ยี่ห้อ -->

    <input
        type="text"
        name="make"
        placeholder="ยี่ห้อ เช่น Toyota"
        value="<?php echo safe($make); ?>"
    >


    <!-- รุ่น -->

    <input
        type="text"
        name="model"
        placeholder="รุ่น เช่น Camry"
        value="<?php echo safe($model); ?>"
    >


    <!-- ปี -->

    <input
        type="number"
        name="year"
        placeholder="ปี เช่น 2024"
        min="1900"
        max="<?php echo date('Y'); ?>"
        value="<?php echo safe($year); ?>"
    >


    <!-- ปุ่มค้นหา -->

    <button
        type="submit"
        name="action"
        value="search"
        class="search-btn"
    >
        🔍 ค้นหารถ
    </button>


    <!-- ปุ่มสุ่ม -->

    <button
        type="submit"
        name="action"
        value="random"
        class="random-btn"
    >
        🎲 สุ่มรถ
    </button>


</form>


<!-- ===============================
     Error Message
================================ -->

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


<!-- ===============================
     Car Information
================================ -->

<?php if ($car): ?>


<div class="card">


    <!-- รูปรถ -->

    <?php if ($image !== ''): ?>

        <img
            src="<?php echo safe($image); ?>"
            class="car-image"
            alt="<?php
                echo safe(
                    $makeValue .
                    ' ' .
                    $modelValue
                );
            ?>"
        >

    <?php else: ?>

        <div class="no-image">

            🚘

        </div>

    <?php endif; ?>


    <!-- ข้อมูลรถ -->

    <div class="car-content">


        <!-- ชื่อรถ -->

        <h2>

            <?php
            echo safe(
                $yearValue
            );
            ?>

            <?php
            echo safe(
                $makeValue
            );
            ?>

            <?php
            echo safe(
                $modelValue
            );
            ?>

            <?php
            echo safe(
                $trimValue
            );
            ?>

        </h2>


        <!-- VIN -->

        <div class="vin">

            VIN:

            <?php
            echo safe(
                $vin
            );
            ?>

        </div>


        <!-- ราคา -->

        <div class="price">

            <?php if (
                $price !== null
                &&
                is_numeric($price)
                &&
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


        <!-- Specs -->

        <div class="spec-grid">


            <!-- Mileage -->

            <div class="spec">

                <div class="spec-title">

                    🛣 เลขไมล์

                </div>

                <div class="spec-value">

                    <?php

                    echo (
                        $miles !== null
                        &&
                        is_numeric($miles)
                    )
                        ? number_format(
                            $miles
                        ) . ' miles'
                        : '-';

                    ?>

                </div>

            </div>


            <!-- Engine -->

            <div class="spec">

                <div class="spec-title">

                    ⚙️ เครื่องยนต์

                </div>

                <div class="spec-value">

                    <?php
                    echo safe(
                        $engine
                    );
                    ?>

                </div>

            </div>


            <!-- Fuel -->

            <div class="spec">

                <div class="spec-title">

                    ⛽ เชื้อเพลิง

                </div>

                <div class="spec-value">

                    <?php
                    echo safe(
                        $fuel
                    );
                    ?>

                </div>

            </div>


            <!-- Transmission -->

            <div class="spec">

                <div class="spec-title">

                    🔄 เกียร์

                </div>

                <div class="spec-value">

                    <?php
                    echo safe(
                        $transmission
                    );
                    ?>

                </div>

            </div>


            <!-- Drivetrain -->

            <div class="spec">

                <div class="spec-title">

                    🚙 ระบบขับเคลื่อน

                </div>

                <div class="spec-value">

                    <?php
                    echo safe(
                        $drivetrain
                    );
                    ?>

                </div>

            </div>


            <!-- Year -->

            <div class="spec">

                <div class="spec-title">

                    📅 ปีรถ

                </div>

                <div class="spec-value">

                    <?php
                    echo safe(
                        $yearValue
                    );
                    ?>

                </div>

            </div>


        </div>


        <!-- Dealer -->

        <div class="dealer">


            <strong>

                🏢 ตัวแทนจำหน่าย:

            </strong>

            <?php
            echo safe(
                $dealer
            );
            ?>


            <?php if (
                $city !== ''
                ||
                $state !== ''
            ): ?>


                <br>


                <strong>

                    📍 สถานที่:

                </strong>


                <?php

                $location =
                    trim(
                        $city .
                        ', ' .
                        $state,
                        ', '
                    );

                echo safe(
                    $location
                );

                ?>


            <?php endif; ?>


            <!-- ===============================
                 ปุ่มใหม่
                 เปิด Detail Page ของเราเอง
            ================================= -->

            <?php if (
                $vin !== '-'
                &&
                preg_match(
                    '/^[A-HJ-NPR-Z0-9]{17}$/i',
                    $vin
                )
            ): ?>


                <br>


                <a
                    href="detail.php?vin=<?php
                        echo urlencode(
                            $vin
                        );
                    ?>"
                    class="view-btn"
                >

                    🚘 ดูรายละเอียดรถ

                </a>


            <?php endif; ?>


        </div>


    </div>


</div>


<?php endif; ?>


<!-- ===============================
     Footer
================================ -->

<footer>

    Data provided by Auto.dev Vehicle Listings API

</footer>


</body>

</html>