<?php

function generateReceiptHTML($data){

$logo="data:image/png;base64,".base64_encode(file_get_contents("../../images/logo.png"));
$paid="data:image/png;base64,".base64_encode(file_get_contents("../../images/paid.png"));

return '

<style>
@page { margin: 40px; }

body{
font-family: DejaVu Sans, sans-serif;
margin:0;
padding:0;
color:#2d3436;
}

.page{ text-align:center; margin-top:20px; }

.container{
width:420px;
margin:0 auto;
text-align:center;
}

.logo{ width:150px; margin-bottom:20px; }

.title{ font-size:36px; font-weight:bold; margin-bottom:8px; }

.subtitle{ font-size:20px; font-weight:600; margin-bottom:10px; }

.gray{ font-size:16px; color:#666; line-height:1.6; }

.company{ margin-top:18px; font-size:16px; color:#666; }

.divider{ margin:25px auto; width:80%; border-top:1px solid #ddd; }

.amount{ font-size:20px; margin-top:20px; }

.amount b{ font-size:28px; }

.method{ margin-top:18px; font-size:14px; color:#666; }

.paid{ margin-top:25px; }

.paid img{ width:160px; }
</style>

<div class="page">
<div class="container">

<img class="logo" src="'.$logo.'">

<div class="title">Payment Receipt</div>

<div class="subtitle">
Invoice #'.$data['invoice_number'].'
</div>

<div class="gray">
for '.$data['first_name'].' '.$data['last_name'].'<br>
paid on '.date("M j, Y",strtotime($data['payment_date'])).'
</div>

<div class="company">
<b>Achievers Castle Learning Centre Ltd.</b><br>
11-102 Cope Crescent<br>
Saskatoon, Saskatchewan S7T 0C7<br>
Canada<br>
(639) 384-2844
</div>

<div class="divider"></div>

<div class="amount">
Payment Amount: <b>$'.$data['amount'].' CAD</b>
</div>

<div class="divider"></div>

<div class="method">
<b>PAYMENT METHOD:</b> '.strtoupper($data['payment_method']).'
</div>

<div class="paid">
<img src="'.$paid.'">
</div>

</div>
</div>
';
}