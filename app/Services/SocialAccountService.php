<?php

namespace App\Services;

use Laravel\Socialite\Contracts\Provider;
use App\Account;
use App\User;
use Auth;

class SocialAccountService
{
    public function setOrGetUser(Provider $provider, $loggedIn)
    {
        $providerUser = $provider->user();
        $providerName = defined(get_class($provider) . '::IDENTIFIER') ? $provider::IDENTIFIER : class_basename($provider);

        $account = Account::whereProvider($providerName)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new Account([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $providerName
            ]);

            $user = null;

            if(!empty($providerUser->getEmail())) {
                $user = User::whereEmail($providerUser->getEmail())
                            ->whereNotNull('password')
                            ->first();

                if(!$user) {
                    $user = User::where('accounts_email', $providerUser->getEmail())->first();
                }
            }

            if(!$user) {
                $user = $loggedIn;
            }

            if (!$user) {
                $user = new User;
                $user->accounts_email = $providerUser->getEmail();
                $names = explode(' ', $providerUser->getName());
                $user->firstname = $names[0];
                $user->lastname = $names[1];
                $user->save();
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }
}