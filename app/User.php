<?php

namespace App;

use App\Admin\Services\UserService;
use App\Models\System\District;
use App\Models\System\Province;
use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Encore\Admin\Auth\Database\HasPermissions;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, AdminBuilder, HasPermissions;

    const STATUS = [
        0   =>  'Khoá',
        1   =>  'Hoạt động'
    ];

    const ADMIN = 0;
    const CUSTOMER = 1;
    const ACTIVE = 1;
    const DEACTIVE = 0;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = "admin_users";

    /**
     * Fields
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'name',
        'avatar',
        'email',
        'phone_number',
        'wallet',
        'address',
        'is_customer',
        'symbol_name',
        'ware_house_id',
        'is_active',
        'password',
        'note',
        'wallet_order',
        'province',
        'district',
        'staff_sale_id',
        'customer_percent_service',
        'type_customer',
        'wallet_weight'
    ];

    protected $casts = [
        'created_at'  => 'datetime:H:i | d-m-Y'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = []){
        $connection = $this->connection ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar){
        if(url()->isValidUrl($avatar)){
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if($avatar && array_key_exists($disk, config('filesystems.disks'))){
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/bamboo-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany{
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany{
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    public function getAccessToken(){
        $token = json_decode($this->token, true);
        if($token && isset($token['access_token'])){
            return $token['access_token'];
        }
        return '';
    }

    public function profile()
    {
        return $this->hasOne('App\Models\UserProfile', 'user_id', 'id');
    }

    public function warehouse() {
        return $this->hasOne('App\Models\System\Warehouse', 'id', 'ware_house_id');
    }

    public function getDistrict() {
        $district = District::where('district_id', $this->district)->first();

        if (! $district) { return null; }

        return $district->type . " " . $district->name;
    }

    public function getProvince() {
        $province = Province::where('province_id', $this->province)->first();

        if (! $province) { return null; }

        return $province->type . " " . $province->name;
    }

    public function saleEmployee() {
        return $this->hasOne('App\User', 'id', 'staff_sale_id');
    }

    public function percentService() {
        return $this->hasOne('App\Models\System\CustomerPercentService', 'id', 'customer_percent_service');
    }

    public function updateWalletByHistory() {
        $service = new UserService();
        $data = $service->GetCustomerTransactionHistory($this->id, false);
        $lastMoney = $data[0]['after_payment'];
        $this->wallet = $lastMoney;
        $this->save();
        return true;
    }
}
