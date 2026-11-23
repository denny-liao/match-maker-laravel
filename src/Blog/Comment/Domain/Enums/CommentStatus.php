<?php

namespace Src\Blog\Comment\Domain\Enums;

enum CommentStatus: string
{
    case Pending   = 'pending';   // 贊助待付款
    case Published = 'published'; // 已公開顯示
}