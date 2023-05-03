<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 1/3/20
 * Time: 5:28 PM
 */

namespace App\Http\Services;

use App\Http\Models\NusalUser;
use App\Http\Services\Outlook\OutlookServiceForAdmin;
use App\Http\Services\Outlook\OutlookServiceForToken;
use App\Http\Services\Outlook\OutlookServiceForUser;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class OutlookService
{

    const TENANT_NURD = 'nurd';
    const TENANT_NUMSP = 'numsp';

    private function __construct()
    {

    }

    /**
     * @param NusalUser $user
     * @return OutlookServiceForUser
     */
    public static function forUser(NusalUser $user){
        return new OutlookServiceForUser($user);
    }

    /**
     * @param $token
     * @return OutlookServiceForToken
     */
    public static function forToken($token){
        return new OutlookServiceForToken($token);
    }

    /**
     * @param null $tenant
     * @return OutlookServiceForAdmin
     */
    public static function forAdmin($tenant = null){
        return new OutlookServiceForAdmin($tenant);
    }

    /**
     * @param string $tenant
     * @return GenericProvider
     */
    public static function getConnectorClientForTenant(string $tenant = 'numsp'){

        $clientId = env('OAUTH_NUMSP_APP_ID');
        $clientSecret = env('OAUTH_NUMSP_APP_SECRET');
        $tenantId = env('OAUTH_NUMSP_TENANT_ID');

        if($tenant === static::TENANT_NURD){
            $clientId = env('OAUTH_NURD_APP_ID');
            $clientSecret = env('OAUTH_NURD_APP_SECRET');
            $tenantId = env('OAUTH_NURD_TENANT_ID');
        }

        return new GenericProvider([
            'clientId'                => $clientId,
            'clientSecret'            => $clientSecret,
            'redirectUri'             => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => env('OAUTH_AUTHORITY') . $tenantId .env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY'). $tenantId . env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => env('OAUTH_SCOPES')
        ]);
    }

    /**
     * @param string $tenant
     * @return GenericProvider
     */
    public static function getConnectorClientForAdmin($tenant = 'numsp')
    {
        $tenantId = static::getTenantId($tenant);
        $clientId = env('OAUTH_'.strtoupper($tenant).'_APP_ID');
        $clientSecret = env('OAUTH_'.strtoupper($tenant).'_APP_SECRET');

        return new GenericProvider([
            'clientId'                => $clientId,
            'clientSecret'            => $clientSecret,
            'urlAuthorize'            => env('OAUTH_AUTHORITY') .$tenantId.env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY'). $tenantId . env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public static function getTenantId($tenant = 'numsp')
    {
        return env('OAUTH_'.strtoupper($tenant).'_TENANT_ID');
    }

    /**
     * @param NusalUser $user
     * @return GenericProvider
     */
    public static function getConnectorClientForUserUser(NusalUser $user){
        return static::getConnectorClientForTenant($user->getTenant());
    }

    /**
     * @param $tokenInfo
     * @param $tenant
     * @return array
     * @throws IdentityProviderException
     */
    public static function checkAndUpdateTenantToken($tokenInfo, $tenant){

        $tokenInfo['renewed'] = false;
        $now = time() + 300;
        if ($tokenInfo['expires'] <= $now) {

            $oauthConnectorClient = static::getConnectorClientForTenant($tenant);
            $newToken = $oauthConnectorClient->getAccessToken('refresh_token', ['refresh_token' => $tokenInfo['refresh_token']]);
            $tokenInfo = [
                'token' => $newToken->getToken(),
                'refresh_token' => $newToken->getRefreshToken(),
                'expires' => $newToken->getExpires(),
                'renewed' => true
            ];
        }
        return $tokenInfo;
    }

    /**
     * @param $tokenInfo
     * @param $tenant
     * @return array
     * @throws IdentityProviderException
     */
    public static function checkAndUpdateAdminToken($tokenInfo, $tenant){

        $tokenInfo['renewed'] = false;
        $now = time() + 300;
        if ($tokenInfo['expires'] <= $now) {

            $oauthConnectorClient = static::getConnectorClientForAdmin($tenant);
            $newToken = $oauthConnectorClient->getAccessToken('client_credentials', ['refresh_token' => $tokenInfo['refresh_token']]);
            $tokenInfo = [
                'token' => $newToken->getToken(),
                'refresh_token' => $newToken->getRefreshToken(),
                'expires' => $newToken->getExpires(),
                'renewed' => true
            ];
        }
        return $tokenInfo;
    }

    /**
     * @param NusalUser $user
     * @throws IdentityProviderException
     */
    public static function checkAndUpdateUserToken(NusalUser $user){
        $tokenInfo = static::checkAndUpdateTenantToken($user->full_oauth_token, $user->getTenant());
        if($tokenInfo['renewed']){
            $user->updateOauthToken($tokenInfo);
        }
    }

}
