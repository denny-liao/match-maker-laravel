<?php

namespace Src\Payment\Domain\Enums;

enum PaymentStatus:string 
{
    case Pending = 'pending'; // 處裡中
    case WaitingPayment = 'waiting_payment'; // 取號等待付款
    case Success = 'success';
    case Expired = 'expired';
    case Failed = 'failed';
}