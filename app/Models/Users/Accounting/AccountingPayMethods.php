<?php

namespace App\Models\Users\Accounting;

use App\Models\BaseModel;
use App\Models\Users\User;
use App\Traits\EncryptsAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingPayMethods extends BaseModel {
    use SoftDeletes;
    use EncryptsAttributes;

    protected $table = "users_accounting_paymethods";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "row_uuid", "user_id", BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT, "user",
    ];

    protected $guarded = [];

    protected array $encrypts = ["paymethod_account"];

    protected $appends = [
        "pay_method_uuid",
    ];

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function getPayMethodUuidAttribute() {
        return ($this->row_uuid);
    }
}
