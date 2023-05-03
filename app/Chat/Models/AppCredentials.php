<?php

namespace App\Chat\Models;

use App\Http\Services\Slack\Models\AccessResponse;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

/**
 * Class AppCredentials
 * @package App\Chat\Models
 * @method AppCredentials|Builder channel($channelSlug)
 * @method AppCredentials|Builder teamId($teamId=null)
 * @method AppCredentials|Builder appId($appId)
 */
class AppCredentials extends Model
{
    use SoftDeletes;

    public $connection = 'mysql';
    public $table = 'chat_app_credentials';
    public $timestamps = true;
    public $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('\App\UserCapabilities\Models\PairProduct', 'product_id', 'id');
    }

    /**
     * @return PairProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param AccessResponse $accessResponse
     * @param $channelSlug
     * @param AppCredentials $appCredentials
     * @return AppCredentials
     */
    public static function createCredentialsByAccessResponse(AccessResponse $accessResponse, $channelSlug, AppCredentials $appCredentials)
    {
        $credentials = new static();

        $credentials
            ->setChannel($channelSlug)
            ->setTeamId($accessResponse->getTeamId())
            ->setAppId($accessResponse->getAppId())
            ->setAppName($appCredentials->getAppName())
            ->setProductId($appCredentials->getProductId())
            ->setClientSecret($appCredentials->getClientSecret())
            ->setClientId($appCredentials->getClientId())
            ->save();

        return $credentials;
    }

    /**
     * @param $query
     * @param $productId
     * @return mixed
     */
    public function scopeProductId($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * @param $query
     * @param $channelName
     * @return mixed
     */
    public function scopeChannel($query, $channelName)
    {
        return $query->where('channel', $channelName);
    }

    /**
     * @param $query
     * @param $appId
     * @return mixed
     */
    public function scopeAppId($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * @param $query
     * @param $teamId
     * @return mixed
     */
    public function scopeTeamId($query, $teamId)
    {
        if(is_null($teamId)){
            return $query;
        }
        return $query->where('team_id', $teamId);
    }

    /**
     * @param $value
     * @return string
     */
    protected function encryptValue($value)
    {
        // null value should not encrypt
        return !is_null($value) ? Crypt::encryptString($value) : $value;
    }

    /**
     * @param $value
     * @return string
     */
    protected function decryptValue($value)
    {
        // null value cannot decrypted
        return !is_null($value) ? Crypt::decryptString($value) : $value;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param $appId
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->app_id = $appId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @param $appName
     * @return $this
     */
    public function setAppName($appName)
    {
        $this->app_name = $appName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppName()
    {
        return $this->app_name;
    }

    /**
     * @param $teamId
     * @return $this
     */
    public function setTeamId($teamId)
    {
        $this->team_id = $teamId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTeamId()
    {
        return $this->team_id;
    }

    /**
     * @param $value
     */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = $this->encryptValue($value);
    }

    /**
     * @param $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getClientIdAttribute($value)
    {
        return $this->decryptValue($value);
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param $value
     */
    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $this->encryptValue($value);
    }

    /**
     * @param $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->client_secret = $clientSecret;
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getClientSecretAttribute($value)
    {
        return $this->decryptValue($value);
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @param $value
     */
    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = $this->encryptValue($value);
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->access_token = $accessToken;
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getAccessTokenAttribute($value)
    {
        return $this->decryptValue($value);
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param $value
     */
    public function setRefreshTokenAttribute($value)
    {
        $this->attributes['refresh_token'] = $this->encryptValue($value);
    }

    /**
     * @param $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refresh_token = $refreshToken;
        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getRefreshTokenAttribute($value)
    {
        return $this->decryptValue($value);
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return config('chat.slack.redirect_uri') . '?channel=slack&product=' . $this->getProduct()->getSlug();
    }
}
