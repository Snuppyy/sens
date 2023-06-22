<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Services\SocialAccountService;
use Socialite;

use Auth;

use App\Account;
use App\Questionnaire;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $input = $request->get($this->username());

        if(filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $field = $this->username();
        } else {
            $field = 'phone';
            $input = preg_replace('/[^0-9]/', '', $input);
            if(strlen($input) < 12) {
                $input = '998' . $input;
            }
        }

        return [
            $field => $input,
            'password' => $request->password,
        ];
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $questionnaires = Questionnaire::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('closed', null)
                    ->orWhere('training_finished', null);
            })
            ->get();

        foreach($questionnaires as $questionnaire) {
                $questionnaire->closed = 1;
                $questionnaire->training_finished = 1;
                $questionnaire->finished_at = $questionnaire->updated_at;
                $questionnaire->timestamps = false;
            $questionnaire->save();
        }

        return redirect()->route('coauth', [
            'token' => Auth::guard('api')->login($user),
            'back' => session()->pull('url.intended', '//' . config('app.main_domain') . $this->redirectPath())
        ]);
   }

    public function redirectToLogin($social)
    {
        return Socialite::with($social)->scopes(['email'])->redirect();
    }

    function handleSocialCallback(SocialAccountService $service, Request $request, $social)
    {
        $loggedin_user = $request->user();

        $user = $service->setOrGetUser(Socialite::driver($social), $request->user());

        if(!$loggedin_user || $user->id == $loggedin_user->id) {
            auth()->login($user);
        }

        return view('auth.social-callback', [
            'redirect' => $loggedin_user ? route('profile') . ($user->id != $loggedin_user->id ? '?social-message' : '') : (
                            $user->level ?
                                $this->redirectTo : '/register/success'
                        )
        ]);

        //return redirect($user->level ? $this->redirectTo : '/register/success');
    }

    function detachSocial(Request $request, $social)
    {
        $provider = Socialite::driver($social);
        $providerName = defined(get_class($provider) . '::IDENTIFIER') ?
            $provider::IDENTIFIER : class_basename($provider);

        Account::whereProvider($providerName)
            ->whereUserId($request->user()->id)
            ->firstOrFail()
            ->delete();

        return redirect(route('profile'));
    }
}
