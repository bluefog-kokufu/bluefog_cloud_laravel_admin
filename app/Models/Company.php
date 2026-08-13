<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    /** プロビジョニング状況の選択肢 */
    public const PROVISION_STATUSES = [
        'pending' => '未着手',
        'active' => '稼働中',
        'failed' => '失敗',
    ];

    protected $fillable = [
        'name',
        'slug',
        'contact_name',
        'contact_email',
        'provision_status',
        'bootstrap_token',
        'provision_error',
        'memo',
    ];

    protected $hidden = [
        'bootstrap_token',
    ];
}
