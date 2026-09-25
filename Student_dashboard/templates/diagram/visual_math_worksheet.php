<?php
declare(strict_types=1);

$q     = $q ?? [];
$index = $index ?? 0;

$qid = (int)($q['id'] ?? 0);

$data = json_decode(
    $q['question_payload'] ?? '{}',
    true
);

if (!is_array($data)) {
    $data = [];
}

$mode = $data['mode'] ?? '';

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$number = ((int)$index + 1);
if (!function_exists('renderVmwShape')) {

function renderVmwShape($symbol, $class = 'vmw-shape-symbol')
{
    $symbol = (string)$symbol;

    switch($symbol){

        case '●':
        case 'circle':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-circle"></span>
                    </span>';

        case '⬟':
        case 'pentagon':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-pentagon"></span>
                    </span>';

        case '▼':
        case 'inverted_triangle':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-triangle-down"></span>
                    </span>';

        case '▲':
        case 'triangle':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-triangle-up"></span>
                    </span>';

        case '⬡':
        case 'hexagon':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-hexagon"></span>
                    </span>';

        case '★':
        case 'star':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-star">★</span>
                    </span>';

        case '□':
        case 'square':
            return '<span class="'.$class.'">
                        <span class="vmw-shape-square"></span>
                    </span>';

        default:
            return '<span class="'.$class.'">'
                .htmlspecialchars(
                    $symbol,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                )
                .'</span>';
    }
}

}

/* =========================================================
   renderWeightIcon() — inline SVG icons for balance_weight
   Ise template me renderVmwShape() ke paas paste karo (top pe).
   Koi image file / hosting nahi chahiye.
========================================================= */
if (!function_exists('renderWeightIcon')) {
function renderWeightIcon($name)
{
    $name = strtolower(trim((string)$name));
    $o = '<svg class="vmw-wt-svgicon" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">';
    $c = '</svg>';

    switch ($name) {

        case 'milk':
            return $o.'
            <polygon points="18,20 46,20 46,58 18,58" fill="#eef4fb" stroke="#aec6de" stroke-width="2"/>
            <polygon points="18,20 32,8 46,20" fill="#d3e3f3" stroke="#aec6de" stroke-width="2"/>
            <rect x="22" y="34" width="20" height="16" fill="#3b82c4"/>
            <text x="32" y="46" font-size="8" font-weight="bold" fill="#fff" text-anchor="middle" font-family="Arial">MILK</text>'.$c;

        case 'honey':
            return $o.'
            <rect x="20" y="26" width="24" height="32" rx="5" fill="#f2b23a" stroke="#c88a1f" stroke-width="2"/>
            <rect x="18" y="18" width="28" height="10" rx="3" fill="#b9761f"/>
            <rect x="25" y="12" width="14" height="7" rx="2" fill="#d99a2f"/>
            <path d="M32 34 q5 7 0 14 q-5 -7 0 -14z" fill="#fff2cf" opacity=".7"/>'.$c;

        case 'backpack':
            return $o.'
            <rect x="16" y="20" width="32" height="34" rx="10" fill="#2fa38f"/>
            <path d="M24 22 q8 -12 16 0" fill="none" stroke="#1f7d6e" stroke-width="3"/>
            <rect x="22" y="34" width="20" height="18" rx="5" fill="#ffd24a"/>
            <rect x="29" y="38" width="6" height="11" rx="2" fill="#1f7d6e"/>'.$c;

        case 'purse':
            return $o.'
            <path d="M16 30 h32 l-3 24 h-26z" fill="#f19bc0"/>
            <path d="M22 30 q10 -16 20 0" fill="none" stroke="#d76ea0" stroke-width="3"/>
            <rect x="28" y="36" width="8" height="5" rx="2" fill="#d76ea0"/>'.$c;

        case 'candy_jar':
            return $o.'
            <rect x="20" y="22" width="24" height="34" rx="6" fill="#f7d0e0" stroke="#e089b0" stroke-width="2"/>
            <rect x="19" y="16" width="26" height="8" rx="2" fill="#e06aa0"/>
            <path d="M32 36 a3 3 0 0 1 6 0 a3 3 0 0 1 0 3 l-6 6 l-6 -6 a3 3 0 0 1 0 -3 a3 3 0 0 1 6 0z" fill="#e0407f"/>'.$c;

        case 'coin_jar':
            return $o.'
            <rect x="20" y="20" width="24" height="36" rx="6" fill="#dff0f5" stroke="#a9cfd9" stroke-width="2"/>
            <rect x="19" y="14" width="26" height="8" rx="2" fill="#8fb9c4"/>
            <circle cx="28" cy="47" r="5" fill="#f2c23a"/>
            <circle cx="37" cy="49" r="5" fill="#e8b02f"/>
            <circle cx="33" cy="41" r="5" fill="#f2c23a"/>'.$c;

        case 'torch':
            return $o.'
            <g transform="rotate(-12 32 36)">
              <rect x="18" y="31" width="26" height="12" rx="3" fill="#333"/>
              <rect x="14" y="33" width="6" height="8" rx="2" fill="#111"/>
              <polygon points="44,29 58,23 58,45 44,41" fill="#f6d24a" opacity=".85"/>
            </g>'.$c;

        case 'clock':
            return $o.'
            <circle cx="22" cy="20" r="5" fill="#3f7fc0"/>
            <circle cx="42" cy="20" r="5" fill="#3f7fc0"/>
            <line x1="26" y1="50" x2="22" y2="57" stroke="#2f6ea5" stroke-width="3"/>
            <line x1="38" y1="50" x2="42" y2="57" stroke="#2f6ea5" stroke-width="3"/>
            <circle cx="32" cy="36" r="16" fill="#5aa0e0" stroke="#2f6ea5" stroke-width="2"/>
            <circle cx="32" cy="36" r="11" fill="#fff"/>
            <line x1="32" y1="36" x2="32" y2="29" stroke="#333" stroke-width="2"/>
            <line x1="32" y1="36" x2="38" y2="38" stroke="#333" stroke-width="2"/>'.$c;

        case 'lock':
            return $o.'
            <path d="M24 30 v-6 a8 8 0 0 1 16 0 v6" fill="none" stroke="#c9a227" stroke-width="4"/>
            <rect x="20" y="30" width="24" height="22" rx="4" fill="#f2c53d" stroke="#c9a227" stroke-width="2"/>
            <circle cx="32" cy="40" r="3" fill="#8a6d16"/>
            <rect x="31" y="41" width="2" height="6" fill="#8a6d16"/>'.$c;

        case 'pot':
            return $o.'
            <path d="M18 34 q0 -9 14 -9 q14 0 14 9" fill="#8a4a26"/>
            <ellipse cx="32" cy="34" rx="14" ry="5" fill="#7fb7d4"/>
            <path d="M18 34 q14 22 28 0z" fill="#a6572e"/>'.$c;

        case 'cup':
            return $o.'
            <path d="M42 32 a6 6 0 0 1 0 12" fill="none" stroke="#e8862e" stroke-width="3"/>
            <path d="M22 30 h20 v9 a10 10 0 0 1 -20 0z" fill="#e8862e"/>
            <ellipse cx="32" cy="30" rx="10" ry="3" fill="#b85f1e"/>
            <ellipse cx="32" cy="49" rx="16" ry="3" fill="#cf7526"/>'.$c;

        case 'vase':
            return $o.'
            <path d="M26 22 h12 l3 8 q4 12 -3 24 h-12 q-7 -12 -3 -24z" fill="#c47a4a"/>
            <rect x="24" y="34" width="16" height="4" fill="#f0d8c0"/>
            <rect x="24" y="42" width="16" height="3" fill="#f0d8c0"/>'.$c;

        case 'caramel':
            return $o.'
            <rect x="22" y="30" width="20" height="16" rx="3" fill="#b5732e"/>
            <rect x="22" y="30" width="20" height="6" rx="3" fill="#cf8c42"/>'.$c;

        case 'candy':
            return $o.'
            <polygon points="22,36 14,30 14,42" fill="#f2a6a0"/>
            <polygon points="42,36 50,30 50,42" fill="#f2a6a0"/>
            <ellipse cx="32" cy="36" rx="10" ry="8" fill="#e0473b"/>'.$c;

        case 'donuts':
            return $o.'
            <ellipse cx="32" cy="45" rx="16" ry="7" fill="#9c6b3f"/><ellipse cx="32" cy="45" rx="5" ry="2" fill="#6f4a29"/>
            <ellipse cx="30" cy="37" rx="16" ry="7" fill="#a5764a"/><ellipse cx="30" cy="37" rx="5" ry="2" fill="#6f4a29"/>
            <ellipse cx="33" cy="29" rx="16" ry="7" fill="#9c6b3f"/><ellipse cx="33" cy="29" rx="5" ry="2" fill="#6f4a29"/>'.$c;

        case 'potato':
            return $o.'
            <ellipse cx="32" cy="36" rx="17" ry="12" fill="#b98a52"/>
            <circle cx="26" cy="32" r="1.6" fill="#8a6338"/>
            <circle cx="36" cy="38" r="1.6" fill="#8a6338"/>
            <circle cx="40" cy="30" r="1.4" fill="#8a6338"/>'.$c;

        case 'trophy':
            return $o.'
            <path d="M24 22 h-5 a5 6 0 0 0 6 8" fill="none" stroke="#e0a92f" stroke-width="3"/>
            <path d="M40 22 h5 a5 6 0 0 1 -6 8" fill="none" stroke="#e0a92f" stroke-width="3"/>
            <path d="M24 18 h16 v8 a8 8 0 0 1 -16 0z" fill="#f2c23d"/>
            <rect x="30" y="34" width="4" height="8" fill="#d99a2f"/>
            <rect x="24" y="42" width="16" height="5" rx="2" fill="#c98a24"/>'.$c;

        default:
            return ''; // unknown -> template emoji/text fallback use karega
    }
}
}

?>


<style>

/* =========================================================
   COMMON
========================================================= */

.vmw-card{
    width:100%;
    box-sizing:border-box;

    background:#fff;

    border-radius:18px;

    padding:30px;

    margin:20px 0;

    box-shadow:0 4px 14px rgba(0,0,0,.08);
}

.vmw-title{
    text-align:left;

    color:#934b3f;

    font-family:Georgia,"Times New Roman",serif;

    font-size:23px;

    font-weight:700;

    margin-bottom:30px;
}

.vmw-text{
    font-size:20px;
    font-weight:700;
    color:#111;
}

.vmw-op{
    font-size:21px;
    font-weight:700;
    margin:0 4px;
}

.vmw-input{
    width:80px;
    height:38px;

    border:none;
    border-bottom:2px solid #222;

    background:transparent;

    outline:none;

    text-align:center;

    font-size:18px;
    font-weight:700;
}

.vmw-input:focus{
    border-bottom:3px solid #1f669c;
}


/* =========================================================
   IMAGE
========================================================= */

.vmw-image{
    width:65px;
    height:65px;

    object-fit:contain;

    display:inline-block;

    vertical-align:middle;
}


/* =========================================================
   SHARED EQUATION
========================================================= */

.vmw-equation{
    display:flex;

    align-items:center;

    justify-content:center;

    flex-wrap:wrap;

    gap:8px;

    font-size:21px;

    font-weight:700;
}


/* =========================================================
   1. SHAPE VALUE
========================================================= */

.vmw-shape-layout{

    display:grid;

    grid-template-columns:minmax(0,1fr) 280px;

    gap:45px;

    align-items:start;
}

.vmw-shape-equations{

    display:flex;

    flex-direction:column;

    gap:20px;

    padding-top:5px;
}

.vmw-shape-equation{
    display:flex;

    align-items:center;

    flex-wrap:wrap;

    gap:9px;

    min-height:55px;

    font-size:22px;

    font-weight:700;
}

.vmw-shape-equation .vmw-sub-number{
    width:38px;

    font-size:20px;

    font-weight:700;

    color:#111;

    flex-shrink:0;
}

.vmw-shape-symbol{
    width:50px;
    height:50px;

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;
}

.vmw-shape-answer-symbol{
    width:50px;
    height:50px;

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;
}

/* =========================================================
   SHAPE COLORS / SHAPES
========================================================= */

.vmw-shape-circle{
    width:32px;
    height:32px;

    background:#E5A15B;

    border-radius:50%;
}

.vmw-shape-pentagon{
    width:38px;
    height:38px;

    background:#7A3F7A;

    clip-path:polygon(
        50% 0%,
        100% 38%,
        82% 100%,
        18% 100%,
        0% 38%
    );
}

.vmw-shape-triangle-down{
    width:0;
    height:0;

    border-left:20px solid transparent;
    border-right:20px solid transparent;

    border-top:36px solid #ED1C24;
}

.vmw-shape-triangle-up{
    width:0;
    height:0;

    border-left:20px solid transparent;
    border-right:20px solid transparent;

    border-bottom:36px solid #ED1C24;
}

.vmw-shape-hexagon{
    width:40px;
    height:36px;

    background:#F7941D;

    clip-path:polygon(
        25% 0%,
        75% 0%,
        100% 50%,
        75% 100%,
        25% 100%,
        0% 50%
    );
}

.vmw-shape-star{
    font-size:43px;
    line-height:1;

    color:#F5A623;

    text-shadow:
        0 0 0 #F5A623;
}

.vmw-shape-square{
    width:30px;
    height:30px;

    background:#fff;

    border:4px solid #111;

    box-sizing:border-box;
}

.vmw-shape-image{

    width:50px;
    height:50px;

    object-fit:contain;
}

.vmw-shape-answers{

    display:flex;

    flex-direction:column;

    gap:20px;

    padding-top:17px;
}

.vmw-shape-answer-row{

    display:flex;

    align-items:center;

    gap:12px;

    font-size:21px;

    font-weight:700;
}

.vmw-shape-answer-symbol{

    width:45px;

    text-align:center;

    font-size:31px;
}

.vmw-answer-box{

    width:130px;
    height:43px;

    border-radius:7px;

    border:2px solid #3f7779;

    background:#eef8f8;

    outline:none;

    text-align:center;

    font-size:18px;

    font-weight:700;
}

.vmw-answer-box:focus{

    border-color:#1f669c;

    box-shadow:0 0 0 2px rgba(31,102,156,.12);
}


/* =========================================================
   2. PICTURE VALUE
========================================================= */

.vmw-picture-grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:28px 55px;
}

.vmw-picture-equation{

    display:flex;

    align-items:center;

    justify-content:center;

    flex-wrap:wrap;

    gap:8px;

    min-height:95px;

    font-size:20px;

    font-weight:700;
}

.vmw-picture-result{

    font-size:21px;

    font-weight:700;
}

.vmw-picture-input{

    width:120px;

    height:48px;

    border:none;

    border-radius:13px;

    background:#f7d5c9;

    outline:none;

    text-align:center;

    font-size:20px;

    font-weight:700;
}

.vmw-picture-input:focus{

    box-shadow:0 0 0 3px rgba(147,75,63,.18);
}


/* =========================================================
   3. SIMPLE BALANCE
========================================================= */

.vmw-simple-grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:45px 60px;
}

.vmw-simple-item{
    min-height:145px;

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    position:relative;
}

.vmw-simple-item .vmw-sub-number{
    align-self:flex-start;
    margin-bottom:8px;
}

.vmw-scale{

    width:100%;

    max-width:500px;

    position:relative;
}

.vmw-pan-row{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:14px;

    margin-bottom:10px;

}

.vmw-pan{
    min-width:120px;
    min-height:45px;

    padding:8px 16px;

    box-sizing:border-box;

    background:#4f999d;

    color:#fff;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:17px;

    font-weight:700;

    text-align:center;
}


/* EQUATION INSIDE PAN */

.vmw-pan-equation{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:6px;

    white-space:nowrap;

}


/* NUMBERS */

.vmw-pan-value{

    font-size:17px;

    font-weight:700;

}


/* PLUS / MINUS / MULTIPLY */

.vmw-pan-op{

    font-size:18px;

    font-weight:700;

    margin:0 2px;

}


/* EQUAL BETWEEN BOTH PANS */

.vmw-pan-equal{

    font-size:20px;

    font-weight:700;

    color:#111;

    flex-shrink:0;

}

.vmw-pan-input{

    width:25px;

    border:none;

    border-bottom:2px solid #fff;

    background:transparent;

    color:#fff;

    outline:none;

    text-align:center;

    font-size:17px;

    font-weight:700;

    margin-left: 0px;
}

.vmw-scale-line{

    height:5px;

    background:#4f999d;

    border-radius:5px;

    position:relative;

    margin:0 35px;
}

.vmw-scale-center{

    position:absolute;

    left:50%;

    top:0;

    width:5px;

    height:35px;

    background:#4f999d;

    transform:translateX(-50%);
}

.vmw-scale-base{

    width:80px;

    height:13px;

    background:#4f999d;

    border-radius:10px;

    margin:30px auto 0;
}


/* =========================================================
   NUMBER BANK BALANCE — IMAGE STYLE (beam + fulcrum + pink bank)
========================================================= */

.vmw-nb-row{
    display:flex;
    align-items:center;
    gap:36px;
    flex-wrap:wrap;
}

.vmw-nb-number{
    font-size:22px;
    font-weight:700;
    color:#111;
    flex-shrink:0;
}

.vmw-nb-scale{
    flex:0 1 430px;         
    max-width:430px;
    min-width:270px;
}

.vmw-nb-boxes{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    margin-bottom:8px;
}

.vmw-nb-gap{ width:44px; }

.vmw-nb-equal{
    font-size:22px;
    font-weight:700;
    color:#111;
    margin:0 14px;
}

.vmw-nb-plus{
    font-size:20px;
    font-weight:700;
    color:#111;
}

/* green boxes sitting on the beam */
.vmw-nb-row .vmw-nb-slot{
    width:54px;
    height:40px;
    border:none;
    border-radius:12px;
    background:#cfe9d2;
    text-align:center;
    font-size:18px;
    font-weight:700;
    outline:none;
    cursor:pointer;
    box-shadow:none;
}
.vmw-nb-row .vmw-nb-slot:focus{ box-shadow:0 0 0 2px #7fbf86; }

/* the balance beam */
.vmw-nb-beam{
    height:6px;
    background:#111;
    border-radius:3px;
}

/* the triangle fulcrum */
.vmw-nb-fulcrum{
    width:0;
    height:0;
    margin:0 auto;
    border-left:28px solid transparent;
    border-right:28px solid transparent;
    border-bottom:38px solid #111;
}

/* pink number bank */
.vmw-nb-bank{
    background:#f2c7be;
    border-radius:4px;
    padding:14px 22px;
    display:flex;
    align-items:center;
    gap:16px;
    flex-shrink:0;
    margin-left:auto;       
}

.vmw-nb-row .vmw-nb-bank-btn{
    min-width:auto;
    padding:2px 6px;
    border:none;
    background:transparent;
    color:#111;
    font-size:22px;
    font-weight:700;
    cursor:pointer;
    transition:.15s;
}
.vmw-nb-row .vmw-nb-bank-btn:hover{ transform:translateY(-2px); }
.vmw-nb-row .vmw-nb-bank-btn.used{ opacity:.3; }

@media(max-width:768px){
    .vmw-nb-row{ gap:18px; }
    .vmw-nb-scale{ flex:1 1 100%; max-width:100%; }
    .vmw-nb-bank{ padding:10px 16px; gap:12px; }
}

/* =========================================================
   5. ARITHMETIC BALANCE — worksheet look (dashed beam + cute blue triangle)
========================================================= */

.vmw-arith-instruction{
    color:#934b3f;
    font-size:16px;
    font-weight:600;
    margin-bottom:26px;
}

.vmw-arith-single{
    display:flex;
    justify-content:center;
    margin-right: 58%;
}

.vmw-arith-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:46px 55px;
}

.vmw-arith-item{
    display:flex;
    align-items:center;
    gap:16px;
}

.vmw-arith-num{
    font-size:20px;
    font-weight:700;
    color:#111;
    flex-shrink:0;
}

.vmw-arith-scale{
    display:flex;
    flex-direction:column;
    align-items:center;
    margin-left: 27px;
}

.vmw-arith-sides{
    display:flex;
    justify-content:space-between;
    align-items:center;
    width:300px;
    min-height:56px;
    margin-bottom:8px;
    font-size:20px;
    font-weight:700;
    color:#111;
}

.vmw-arith-expr{
    display:flex;
    align-items:center;
    gap:7px;
}

.vmw-arith-beam{
    width:300px;
    border-top:4px dashed #111;
}

.vmw-arith-fulcrum{
    width:66px;
    height:53px;
    margin-top:3px;
}

/* peach circle blank */
.vmw-circle-input{
    width:54px;
    height:54px;
    border:none;
    border-radius:50%;
    background:#efb0a3;
    outline:none;
    text-align:center;
    font-size:19px;
    font-weight:700;
    color:#111;
}
.vmw-circle-input:focus{
    box-shadow:0 0 0 3px rgba(147,75,63,.20);
}

@media(max-width:768px){
    .vmw-arith-grid{ grid-template-columns:1fr; }
    .vmw-arith-sides,
    .vmw-arith-beam{ width:250px; }
}

/* =========================================================
   6. BALANCE WEIGHT — worksheet look (compact, objects on pans)
========================================================= */

.vmw-weight-instruction{
    color:#934b3f;
    font-size:16px;
    font-weight:600;
    margin-bottom:20px;
}

.vmw-weight-single{
    display:flex;
    justify-content:center;
    margin-right: 60%;
}

.vmw-weight-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px 28px;
}

.vmw-weight-item{
    display:flex;
    align-items:flex-start;
    gap:8px;
}

.vmw-weight-num{
    font-size:20px;
    font-weight:700;
    color:#111;
    padding-top:6px;
    flex-shrink:0;
}

.vmw-weight-graphic{
    position:relative;
    width:300px;
    height:175px;     
}

.vmw-wt-lbl-left{ left:70px; }
.vmw-wt-lbl-right{ left:230px; }

.vmw-weight-val{
    background:#f0c9b8;
    border-radius:4px;
    padding:4px 8px;
    font-size:14px;
    font-weight:700;
    color:#111;
    white-space:nowrap;
}

.vmw-weight-blank{
    display:inline-flex;
    align-items:center;
    gap:3px;
    background:#dbe7f5;
    border-radius:4px;
    padding:3px 6px;
}

.vmw-weight-input{
    width:28px;
    border:none;
    border-bottom:2px solid #7a97b5;
    background:transparent;
    outline:none;
    text-align:center;
    font-size:14px;
    font-weight:700;
    color:#111;
}

.vmw-weight-unit{
    font-size:13px;
    font-weight:700;
}

.vmw-wt-svgicon{ width:46px; height:46px; display:block; }

.vmw-weight-svg{
    position:absolute;
    bottom:0;
    left:0;
    width:300px;
    height:150px;
    display:block;
    z-index:1;          
}

/* objects scale ke UPAR */
.vmw-wt-obj-left,
.vmw-wt-obj-right{
    position:absolute;
    bottom:84px;     
    transform:translateX(-50%);
    display:flex;
    align-items:flex-end;
    gap:0;
    line-height:1;
    font-size:36px;
    z-index:3;
}

/* labels bhi upar */
.vmw-wt-lbl-left,
.vmw-wt-lbl-right{
    position:absolute;
    top:6px;
    transform:translateX(-50%);
    display:flex;
    align-items:center;
    gap:5px;
    z-index:3;         
}

.vmw-wt-obj-left{ left:70px; }
.vmw-wt-obj-right{ left:230px; }

.vmw-wt-img{ height:52px; width:auto; object-fit:contain; display:block; }

@media(max-width:768px){
    .vmw-weight-grid{ grid-template-columns:1fr; }
}

/* =========================================================
   7. PICTURE ALGEBRA - SINGLE QUESTION
========================================================= */

.vmw-picture-algebra-single{

    width:100%;

    box-sizing:border-box;

    margin:0;

    padding:0;

}


/* MAIN CARD */

.vmw-picture-algebra-card{

    width:100%;

    box-sizing:border-box;

    background:#fff;

    border-radius:18px;

    padding:30px;

    margin:20px 0;

    box-shadow:0 4px 14px rgba(0,0,0,.08);

    /* IMPORTANT */
    position:relative;

    left:0;

}


/* TITLE */

.vmw-picture-algebra-title{

    text-align:left;

    color:#934b3f;

    font-family:Georgia,"Times New Roman",serif;

    font-size:23px;

    font-weight:700;

    margin-bottom:28px;

}


/* EQUATIONS */

.vmw-picture-algebra-equations{

    display:flex;

    flex-direction:column;

    align-items:flex-start;

    justify-content:flex-start;

    gap:12px;

    width:100%;

}


/* EACH EQUATION */

.vmw-picture-algebra-equation{

    display:flex;

    align-items:center;

    justify-content:flex-start;

    gap:10px;

    min-height:58px;

    font-size:22px;

    font-weight:700;

    color:#111;

    white-space:nowrap;

}


/* PICTURE */

.vmw-picture-algebra-picture{

    width:58px;

    height:58px;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:43px;

    line-height:1;

    flex-shrink:0;

}


/* OPERATOR */

.vmw-picture-algebra-op{

    font-size:23px;

    font-weight:700;

    color:#111;

    flex-shrink:0;

}


/* NUMBER */

.vmw-picture-algebra-number{

    font-size:23px;

    font-weight:700;

    color:#111;

    flex-shrink:0;

}


/* ANSWER BOX */

.vmw-picture-algebra-input{

    width:115px;

    height:48px;

    border:none;

    border-radius:12px;

    background:#f7d5c9;

    outline:none;

    text-align:center;

    font-size:21px;

    font-weight:700;

    flex-shrink:0;

}


.vmw-picture-algebra-input:focus{

    box-shadow:
        0 0 0 3px
        rgba(147,75,63,.18);

}


@media(max-width:768px){

    .vmw-picture-algebra-card{

        padding:20px;

    }

    .vmw-picture-algebra-title{

        font-size:19px;

    }

    .vmw-picture-algebra-equation{

        gap:7px;

        font-size:18px;

    }

    .vmw-picture-algebra-picture{

        width:45px;

        height:45px;

        font-size:33px;

    }

    .vmw-picture-algebra-op{

        font-size:19px;

    }

    .vmw-picture-algebra-number{

        font-size:19px;

    }

    .vmw-picture-algebra-input{

        width:90px;

        height:40px;

        font-size:18px;

    }

}
/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .vmw-card{

        padding:18px;

    }

    .vmw-title{

        font-size:19px;

    }

    .vmw-shape-layout{

        grid-template-columns:1fr;

        gap:25px;

    }

    .vmw-picture-grid,
    .vmw-simple-grid,
    .vmw-arithmetic-grid,
    .vmw-weight-grid{

        grid-template-columns:1fr;

    }

    .vmw-shape-equation{

        font-size:18px;

    }

    .vmw-shape-symbol{

        font-size:28px;

    }

    .vmw-picture-equation{

        font-size:18px;

    }

    .vmw-pan{

        min-width:100px;

    }

        .vmw-pan{

        min-width:105px;

        padding:7px 10px;

        font-size:15px;

    }

    .vmw-pan-value{

        font-size:15px;

    }

    .vmw-pan-op{

        font-size:16px;

    }

    .vmw-pan-equal{

        font-size:18px;

    }

}

</style>


<!-- =========================================================
     1. SHAPE VALUE
========================================================= -->

<?php if($mode === 'shape_value'): ?>

<div class="vmw-card">

<div class="vmw-title">
    <?= $number ?>)
    <?= $h($data['title'] ?? 'Find the values of the shapes.') ?>
</div>


    <div class="vmw-shape-layout">

        <!-- LEFT EQUATIONS -->

        <div class="vmw-shape-equations">

    <?php foreach(($data['equations'] ?? []) as $eqIndex => $equation): ?>

        <div class="vmw-shape-equation">

         
                    <?php foreach($equation as $part): ?>

                        <?php
                        /*
                         * Currently SQL uses text symbols like:
                         * ⬡ ▲ ★ □
                         *
                         * If later you want image shapes,
                         * you can use:
                         *
                         * {"type":"image","src":"..."}
                         */

                        if(is_array($part)):

                            if(($part['type'] ?? '') === 'image'):
                        ?>

                                <img
                                    src="<?= $h($part['src'] ?? '') ?>"
                                    class="vmw-shape-image"
                                    alt=""
                                >

                            <?php else: ?>

                                <span class="vmw-text">
                                    <?= $h($part['value'] ?? '') ?>
                                </span>

                            <?php endif; ?>

                     <?php else: ?>

        <?php if(in_array($part, ['+','-','×','*','='])): ?>

            <span class="vmw-op">
                <?= $h($part === '*' ? '×' : $part) ?>
            </span>

        <?php else: ?>

            <?= renderVmwShape($part, 'vmw-shape-symbol') ?>

        <?php endif; ?>

    <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- RIGHT ANSWERS -->

        <div class="vmw-shape-answers">

            <?php foreach(($data['answers'] ?? []) as $ai => $answer): ?>

                <div class="vmw-shape-answer-row">

                    <?php
                    $symbol = $answer['symbol'] ?? '';
                    ?>

                    <?php if(is_array($symbol)): ?>

                        <?php if(($symbol['type'] ?? '') === 'image'): ?>

                            <img
                                src="<?= $h($symbol['src'] ?? '') ?>"
                                class="vmw-shape-image"
                                alt=""
                            >

                        <?php else: ?>

                            <span class="vmw-shape-answer-symbol">
                                <?= $h($symbol['value'] ?? '') ?>
                            </span>

                        <?php endif; ?>

                  <?php else: ?>

        <?= renderVmwShape($symbol, 'vmw-shape-answer-symbol') ?>

    <?php endif; ?>


                    <span>=</span>


                    <input
                        type="text"
                        class="vmw-answer-box"
                        name="answer[<?= $qid ?>][<?= $ai ?>]"
                        autocomplete="off"
                    >

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
     2. PICTURE VALUE
========================================================= -->

<?php if($mode === 'picture_value'): ?>

<div class="vmw-card">

    <div class="vmw-title">
        <?= $h($data['title'] ?? 'Find the value of the pictures.') ?>
    </div>


    <div class="vmw-picture-grid">

<?php foreach(($data['equations'] ?? []) as $qi => $equation): ?>

    <div class="vmw-picture-equation">

        <span class="vmw-sub-number">
            <?= ($qi + 1) ?>)
        </span>

                <?php foreach(($equation['left'] ?? []) as $part): ?>

                    <?php if(is_array($part)): ?>

                        <?php if(($part['type'] ?? '') === 'image'): ?>

                            <img
                                src="<?= $h($part['src'] ?? '') ?>"
                                class="vmw-image"
                                alt=""
                            >

                        <?php else: ?>

                            <span class="vmw-text">
                                <?= $h($part['value'] ?? '') ?>
                            </span>

                        <?php endif; ?>

                    <?php else: ?>

                        <span class="vmw-op">
                            <?= $h($part === '*' ? '×' : $part) ?>
                        </span>

                    <?php endif; ?>

                <?php endforeach; ?>


                <span class="vmw-op">=</span>


                <?php if(
                    ($equation['result'] ?? '') === '___'
                    || ($equation['result'] ?? '') === ''
                ): ?>

                    <input
                        type="text"
                        class="vmw-picture-input"
                        name="answer[<?= $qid ?>][<?= $qi ?>]"
                        autocomplete="off"
                    >

                <?php else: ?>

                    <span class="vmw-picture-result">
                        <?= $h($equation['result']) ?>
                    </span>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<!-- =========================================================
     3. SIMPLE BALANCE
========================================================= -->

<?php if($mode === 'balance_simple'): ?>

<div class="vmw-card">

    <!-- TITLE + QUESTION NUMBER -->

    <div class="vmw-title">
        <?= $number ?>)
        <?= $h($data['title'] ?? 'BALANCE THE SCALE') ?>
    </div>


    <div class="vmw-simple-grid">

        <?php foreach(($data['questions'] ?? []) as $qi => $question): ?>

            <div class="vmw-simple-item">

                <!-- SCALE -->

                <div class="vmw-scale">

                    <!-- PAN ROW -->

                    <div class="vmw-pan-row">

                        <!-- LEFT PAN -->

                        <div class="vmw-pan">

                            <div class="vmw-pan-equation">

                                <?php foreach(
                                    ($question['left'] ?? []) as $part
                                ): ?>

                                    <?php if(
                                        $part === '+'
                                        || $part === '-'
                                        || $part === '×'
                                        || $part === '*'
                                        || $part === '='
                                    ): ?>

                                        <span class="vmw-pan-op">
                                            <?= $h(
                                                $part === '*'
                                                ? '×'
                                                : $part
                                            ) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="vmw-pan-value">
                                            <?= $h($part) ?>
                                        </span>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </div>

                        </div>


                        <!-- EQUAL SIGN -->

                        <span class="vmw-pan-equal">
                            =
                        </span>


                        <!-- RIGHT PAN -->

                        <div class="vmw-pan">

                        <div class="vmw-pan-equation">

                            <?php foreach(
                                ($question['right'] ?? []) as $part
                            ): ?>

                                <?php if($part === '___'): ?>

                                    <input
                                        type="text"
                                        class="vmw-pan-input"
                                        name="answer[<?= $qid ?>]"
                                        autocomplete="off"
                                    >

                                <?php elseif(
                                    $part === '+'
                                    || $part === '-'
                                    || $part === '×'
                                    || $part === '*'
                                    || $part === '='
                                ): ?>

                                    <span class="vmw-pan-op">
                                        <?= $h(
                                            $part === '*'
                                            ? '×'
                                            : $part
                                        ) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="vmw-pan-value">
                                        <?= $h($part) ?>
                                    </span>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </div>

                    </div>

                    </div>


                    <!-- SCALE BEAM -->

                    <div class="vmw-scale-line">

                        <div class="vmw-scale-center"></div>

                    </div>


                    <!-- SCALE BASE -->

                    <div class="vmw-scale-base"></div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<!-- =========================================================
     4. NUMBER BANK  (image style)
========================================================= -->

<?php if($mode === 'balance_number_bank'): ?>

<?php
    // single question per row via "numbers", ya multiple via "questions"
    $nbQuestions = $data['questions']
        ?? [ ['numbers' => ($data['numbers'] ?? [])] ];
?>

<div class="vmw-card">

    <?php if(!empty($data['title'])): ?>
        <div class="vmw-title"><?= $h($data['title']) ?></div>
    <?php endif; ?>

    <?php if(!empty($data['instruction'])): ?>
        <div style="
            text-align:left;
            color:#934b3f;
            font-size:16px;
            font-weight:600;
            margin-bottom:22px;
        ">
            <?= $h($data['instruction']) ?>
        </div>
    <?php endif; ?>


    <div style="display:flex;flex-direction:column;gap:34px;">

        <?php foreach($nbQuestions as $qi => $question): ?>

            <?php
            $label  = (count($nbQuestions) === 1) ? $number : ($qi + 1);
            $bankId = $qid . '_' . $qi;   // unique per card
            ?>

            <div class="vmw-nb-row">

                <!-- QUESTION NUMBER -->
                <div class="vmw-nb-number"><?= $h($label) ?>)</div>

                <!-- SCALE GRAPHIC -->
                <div class="vmw-nb-scale">

                   <div class="vmw-nb-boxes">

                        <input type="text" readonly class="vmw-bank-slot vmw-nb-slot"
                            data-bank-question="<?= $bankId ?>"
                            name="answer[<?= $qid ?>][0]" autocomplete="off">

                        <span class="vmw-nb-plus">+</span>

                        <input type="text" readonly class="vmw-bank-slot vmw-nb-slot"
                            data-bank-question="<?= $bankId ?>"
                            name="answer[<?= $qid ?>][1]" autocomplete="off">

                        <span class="vmw-nb-equal">=</span>          <!-- 👈 gap ki jagah equal sign -->

                        <input type="text" readonly class="vmw-bank-slot vmw-nb-slot"
                            data-bank-question="<?= $bankId ?>"
                            name="answer[<?= $qid ?>][2]" autocomplete="off">

                        <span class="vmw-nb-plus">+</span>

                        <input type="text" readonly class="vmw-bank-slot vmw-nb-slot"
                            data-bank-question="<?= $bankId ?>"
                            name="answer[<?= $qid ?>][3]" autocomplete="off">

                    </div>

                    <div class="vmw-nb-beam"></div>
                    <div class="vmw-nb-fulcrum"></div>

                </div>

                <!-- PINK NUMBER BANK -->
                <div class="vmw-nb-bank">
                    <?php foreach(($question['numbers'] ?? []) as $num): ?>
                        <button type="button"
                            class="vmw-bank-button vmw-nb-bank-btn"
                            data-bank-question="<?= $bankId ?>"
                            data-number="<?= $h($num) ?>">
                            <?= $h($num) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<!-- =========================================================
     5. ARITHMETIC BALANCE  (worksheet look + numbering)
========================================================= -->

<?php if($mode === 'balance_arithmetic'): ?>

<?php $arithQs = $data['questions'] ?? []; ?>

<div class="vmw-card">

    <?php if(!empty($data['title'])): ?>
        <div class="vmw-title"><?= $h($data['title']) ?></div>
    <?php endif; ?>

    <?php if(!empty($data['instruction'])): ?>
        <div class="vmw-arith-instruction"><?= $h($data['instruction']) ?></div>
    <?php endif; ?>

    <div class="<?= (count($arithQs) === 1) ? 'vmw-arith-single' : 'vmw-arith-grid' ?>">

        <?php foreach($arithQs as $qi => $question): ?>

            <?php
            $label     = (count($arithQs) === 1) ? $number : ($qi + 1);
            $blankName = 'answer[' . $qid . ']'
                       . ((count($arithQs) === 1) ? '' : '[q' . $qi . ']');
            ?>

            <div class="vmw-arith-item">

                <div class="vmw-arith-num"><?= $h($label) ?>)</div>

                <div class="vmw-arith-scale">

                    <div class="vmw-arith-sides">

                        <!-- LEFT -->
                        <div class="vmw-arith-expr">
                            <?php foreach(($question['left'] ?? []) as $part): ?>
                                <?php if($part === '___'): ?>
                                    <input type="text" class="vmw-circle-input"
                                        name="<?= $blankName ?>" autocomplete="off">
                                <?php elseif(in_array($part, ['+','-','×','*'], true)): ?>
                                    <span><?= $h($part === '*' ? '×' : $part) ?></span>
                                <?php else: ?>
                                    <span><?= $h($part) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- RIGHT -->
                        <div class="vmw-arith-expr">
                            <?php foreach(($question['right'] ?? []) as $part): ?>
                                <?php if($part === '___'): ?>
                                    <input type="text" class="vmw-circle-input"
                                        name="<?= $blankName ?>" autocomplete="off">
                                <?php elseif(in_array($part, ['+','-','×','*'], true)): ?>
                                    <span><?= $h($part === '*' ? '×' : $part) ?></span>
                                <?php else: ?>
                                    <span><?= $h($part) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                    </div>

                    <div class="vmw-arith-beam"></div>

                    <!-- CUTE BLUE TRIANGLE (fulcrum) -->
                    <svg class="vmw-arith-fulcrum" viewBox="0 0 90 72"
                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M41 8 Q45 3 49 8 L84 62 Q87 67 81 67 L9 67 Q3 67 6 62 Z"
                              fill="#3ba0e0"/>
                        <circle cx="37" cy="52" r="8"   fill="#ffffff"/>
                        <circle cx="55" cy="52" r="8"   fill="#ffffff"/>
                        <circle cx="38" cy="53" r="4"   fill="#12405f"/>
                        <circle cx="56" cy="53" r="4"   fill="#12405f"/>
                        <circle cx="36.5" cy="51" r="1.4" fill="#ffffff"/>
                        <circle cx="54.5" cy="51" r="1.4" fill="#ffffff"/>
                    </svg>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<!-- =========================================================
     6. WEIGHT BALANCE  (pans hanging + objects inside + numbering)
========================================================= -->

<?php if($mode === 'balance_weight'): ?>

<?php
$wUnit      = $data['unit'] ?? '';
$wQuestions = $data['questions'] ?? [];
?>

<div class="vmw-card">

    <?php if(!empty($data['title'])): ?>
        <div class="vmw-title"><?= $h($data['title']) ?></div>
    <?php endif; ?>

    <!-- instruction yahan se hata diya gaya hai -->

    <div class="<?= (count($wQuestions) === 1) ? 'vmw-weight-single' : 'vmw-weight-grid' ?>">

        <?php foreach($wQuestions as $qi => $question): ?>

            <?php
            $label     = (count($wQuestions) === 1) ? $number : ($qi + 1);
            $blankName = 'answer[' . $qid . ']'
                       . ((count($wQuestions) === 1) ? '' : '[' . $qi . ']');
            $qUnit     = $question['unit'] ?? $wUnit;

            $renderSide = function($parts) use ($h, $blankName, $qUnit) {
                $out = '';
                foreach(($parts ?? []) as $part){
                    if($part === '___'){
                        $out .= '<span class="vmw-weight-blank">'
                              . '<input type="text" class="vmw-weight-input" name="'.$blankName.'" autocomplete="off">'
                              . ($qUnit !== '' ? '<span class="vmw-weight-unit">'.$h($qUnit).'</span>' : '')
                              . '</span>';
                    } else {
                        $out .= '<span class="vmw-weight-val">'.$h($part).'</span>';
                    }
                }
                return $out;
            };
                        // objects: known name -> SVG icon, warna emoji/text
          $renderItems = function($items) use ($h) {
            $out = '';
            foreach(($items ?? []) as $it){
                $s = (string)$it;
                if (strpos($s,'/') !== false || preg_match('~\.(png|jpe?g|gif|svg|webp)$~i',$s)) {
                    $out .= '<img src="'.$h($s).'" class="vmw-wt-img" alt="">';
                } else {
                    $icon = renderWeightIcon($s);
                    $out .= ($icon !== '') ? $icon : '<span>'.$h($s).'</span>';
                }
            }
            return $out;
        };
            ?>

            <div class="vmw-weight-item">

                <div class="vmw-weight-num"><?= $h($label) ?>)</div>

                <?php $isTan = (($data['scale'] ?? '') === 'tan'); ?>
<div class="vmw-weight-graphic <?= $isTan ? 'vmw-wt-tan' : '' ?>">

    <!-- WEIGHT LABELS -->
    <div class="vmw-wt-lbl-left"><?= $renderSide($question['left'] ?? []) ?></div>
    <div class="vmw-wt-lbl-right"><?= $renderSide($question['right'] ?? []) ?></div>

    <!-- OBJECTS PAN KE ANDAR (conversion me khaali) -->
    <div class="vmw-wt-obj-left"><?= $renderItems($question['left_items'] ?? []) ?></div>
    <div class="vmw-wt-obj-right"><?= $renderItems($question['right_items'] ?? []) ?></div>

    <?php if($isTan): ?>
        <!-- TAN CONVERSION SCALE (worksheet jaisa) -->
        <svg class="vmw-weight-svg" viewBox="0 0 300 210"
             xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <g fill="#c99a6b">
                <!-- upar do trays (pans) -->
                <path d="M34 22 Q70 38 106 22 Q70 30 34 22 Z"/>
                <path d="M194 22 Q230 38 266 22 Q230 30 194 22 Z"/>
                <!-- pan supports -->
                <rect x="68" y="34" width="4" height="16"/>
                <rect x="228" y="34" width="4" height="16"/>
                <!-- beam + knobs -->
                <rect x="56" y="49" width="188" height="8" rx="4"/>
                <circle cx="58" cy="53" r="4.5"/>
                <circle cx="242" cy="53" r="4.5"/>
                <circle cx="150" cy="53" r="6"/>
                <!-- tall central post -->
                <rect x="144" y="57" width="12" height="94"/>
                <!-- flared base -->
                <path d="M143 149 L157 149 L188 182 L112 182 Z"/>
            </g>
            <!-- foot bar (thoda dark) -->
            <rect x="106" y="180" width="88" height="13" rx="6" fill="#b98c5b"/>
        </svg>
    <?php else: ?>
        <!-- DEFAULT BLACK SCALE (objects wali worksheet ke liye — same) -->
        <svg class="vmw-weight-svg" viewBox="0 0 300 150"
             xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M30 66 L110 66 Q70 88 30 66 Z" fill="#111"/>
            <path d="M190 66 L270 66 Q230 88 190 66 Z" fill="#111"/>
            <path d="M70 82 Q150 102 230 82" fill="none" stroke="#111"
                  stroke-width="7" stroke-linecap="round"/>
            <rect x="147" y="96" width="6" height="28" fill="#111"/>
            <path d="M150 118 L176 142 L124 142 Z" fill="#111"/>
            <rect x="118" y="140" width="64" height="9" rx="4" fill="#111"/>
            <circle cx="150" cy="98" r="4" fill="black"/>
        </svg>
    <?php endif; ?>

</div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>

<?php if($mode === 'picture_algebra_single'): ?>

<div class="vmw-picture-algebra-single">

    <div class="vmw-picture-algebra-card">

        <div class="vmw-picture-algebra-title">

            <?= $number ?>)
            <?= $h(
                $data['title']
                ?? 'Find the values of the pictures.'
            ) ?>

        </div>

        <div class="vmw-picture-algebra-equations">

            <?php foreach(
                ($data['equations'] ?? []) as $eqIndex => $equation
            ): ?>

                <div class="vmw-picture-algebra-equation">

                    <?php foreach($equation as $part): ?>

                        <?php if($part === '+'): ?>

                            <span class="vmw-picture-algebra-op">
                                +
                            </span>

                        <?php elseif($part === '-'): ?>

                            <span class="vmw-picture-algebra-op">
                                -
                            </span>

                        <?php elseif(
                            $part === '×' ||
                            $part === '*'
                        ): ?>

                            <span class="vmw-picture-algebra-op">
                                ×
                            </span>

                        <?php elseif($part === '='): ?>

                            <span class="vmw-picture-algebra-op">
                                =
                            </span>

                        <?php elseif($part === '___'): ?>

                            <input
                                type="text"
                                class="vmw-picture-algebra-input"
                                name="answer[<?= $qid ?>]"
                                autocomplete="off"
                            >

                        <?php elseif(is_numeric($part)): ?>

                            <span class="vmw-picture-algebra-number">
                                <?= $h($part) ?>
                            </span>

                        <?php else: ?>

                            <span class="vmw-picture-algebra-picture">
                                <?= $h($part) ?>
                            </span>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php endif; ?>

<script>
if (!window.__vmwBankBound) {
    window.__vmwBankBound = true;

    document.addEventListener('click', function (e) {

        /* ---- number bank button: agla khaali box bharo ---- */
        var btn = e.target.closest('.vmw-bank-button');
        if (btn) {
            var q   = btn.getAttribute('data-bank-question');
            var num = btn.getAttribute('data-number');

            var slots = document.querySelectorAll(
                '.vmw-bank-slot[data-bank-question="' + q + '"]'
            );

            for (var i = 0; i < slots.length; i++) {
                if (slots[i].value.trim() === '') {
                    slots[i].value = num;
                    btn.classList.add('used');
                    break;              // sirf pehla khaali box
                }
            }
            return;
        }

        /* ---- filled box par click: clear karo ---- */
        var slot = e.target.closest('.vmw-bank-slot');
        if (slot) {
            if (slot.value.trim() === '') return;

            var q2   = slot.getAttribute('data-bank-question');
            var num2 = slot.value;
            slot.value = '';

            var btns = document.querySelectorAll(
                '.vmw-bank-button[data-bank-question="' + q2 + '"]'
            );
            for (var j = 0; j < btns.length; j++) {
                if (btns[j].getAttribute('data-number') === num2) {
                    btns[j].classList.remove('used');
                    break;
                }
            }
        }
    });
}
</script>