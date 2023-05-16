<?php
session_start();
if (strlen($_SESSION["uid"]) == "") {
    header("location:logout.php");
}

include_once "function.php";
$obj = new DB_con();

include "execute.php";

if (isset($_GET["status"])) {
    if ($_GET["status"] == "success") {
        $result_data = execute($_GET["paymentID"]);
        $response = json_decode($result_data, true);

        if (
            isset($response["statusCode"]) &&
            $response["statusCode"] != "0000"
        ) {
            // Error case
            //echo $response['statusMessage'];
            header(
                "Location: recharge.php?statusMessage=" .
                    $response["statusMessage"]
            );
            exit();
        } else {
            // db insert operation strore $response data
            $id = $_SESSION["uid"];
            $deposit = $response["amount"];

            if ($deposit >= 5 && $deposit < 10) {
                $deposit = $deposit + 1;
            }
            if ($deposit >= 10) {
                $deposit = $deposit + 2;
            }

            $result = $obj->insert_deposit($deposit, $id);


            if ($result) {
                $amount2 = $response["amount"];
                if ($amount2  >= 5 && $amount2 < 10) {
                    $amount2 = $amount2 + 1;
                }
                if ($amount2 >= 10) {
                    $amount2 = $amount2  + 2;
                }

                $user_id = $id;
                $username = $_SESSION["username"];
                $paymentID = $response["paymentID"];
                $payerReference = $response["payerReference"];
                $customerMsisdn = $response["customerMsisdn"];
                $trxID = $response["trxID"];
                $amount = $amount2;
                $merchantInvoiceNumber = $response["merchantInvoiceNumber"];
                $paymentExecuteTime = $response["paymentExecuteTime"];

                $result2 = $obj->bkash_pay(
                    $user_id,
                    $username,
                    $paymentID,
                    $payerReference,
                    $customerMsisdn,
                    $trxID,
                    $amount,
                    $merchantInvoiceNumber,
                    $paymentExecuteTime
                );
                if ($result2) {
                    echo "bks log ok";
                }

                header(
                    "Location: recharge.php?trxID=" . $response["trxID"]
                );
            } else {
                header(
                    "Location: recharge.php?balance_add=faild&trxID=" .
                        $response["trxID"]
                );
            }

            exit();
        }
    } else {
        header("Location: recharge.php");
        exit();
    }
}
