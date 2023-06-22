<?php

namespace App\Http\Controllers\Auth;

use App\User;

use App\Http\Controllers\Controller;
use App\Lib\SMSSender;
use App\TrainingApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;

use Illuminate\Support\Facades\Redirect;

use Jrean\UserVerification\Traits\VerifiesUsers;
use Jrean\UserVerification\Facades\UserVerification;

class RegisterController extends Controller
{
    use RegistersUsers, VerifiesUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/register/success';

    protected $redirectIfVerified = '/home';
    protected $verificationErrorView = 'auth.email-verification.error';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('throttle:6,1')->only('verificationEmail');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $user = null;

        if($token = Input::old('token')) {
            $user = User::where('registration_token', $token)->first();
        }

        return view('auth.register', [
            'phone' => $user ? $user->phone : null,
            'phone_verified' => $user && $user->phone_verified_at,
            'email' => $user ? $user->email : null,
            'email_verified' => $user && $user->verified,
        ]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPhoneForm()
    {
        $user = null;

        if($token = Input::old('token')) {
            $user = User::where('registration_token', $token)->first();
        }

        return view('auth.register-phone', [
            'phone' => $user ? $user->phone : null,
        ]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showCodeForm()
    {
        return view('auth.register-code');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPasswordForm()
    {
        return view('auth.register-password');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = User::where('registration_token', $request->input('token'))->firstOrFail();

        if($user->verified) {
            $accounts_user = User::where('accounts_email', $user->email)->first();

            if($accounts_user) {
                $accounts_user->email = $user->email;
                $accounts_user->verified = $user->verified;
                $accounts_user->email_verified_at = $user->email_verified_at;
                $accounts_user->accounts_email = null;
                $accounts_user->phone = $user->phone;
                $accounts_user->phone_verified_at = $user->phone_verified_at;

                $user->delete();
                $user = $accounts_user;
            }
        } else {
            $user->email = null;
        }

        if(!$user->phone_verified_at) {
            $user->phone = null;
        }

        $user->password = Hash::make($request->input('password'));

        if(in_array($request->input('promocode'), ['9617', '9167'])) {
            $user->role = 'user,participant';
        }

        if($request->input('promocode') == '7625') {
            $user->role = 'user,adjudicator';
        }

        $user->save();

        if($request->input('promocode') == '9167') {
            $application = new TrainingApplication();

            $application->user_id = $user->id;
            $application->training_id = 55;
            $application->status = 'accepted';
            $application->application = [
                'training' => 55,
                'confirm' => 1,
                'submit' => null,
                'flagged' => true,
                'filled' => true,
                'rated' => 1
            ];
            $application->filled = 1;
            $application->rated = 1;
            $application->flagged = 1;
            $application->selected = 1;

            $application->save();
        }

        $this->guard()->login($user);

        /*
        return redirect()->route('coauth', [
            'token' => Auth::guard('api')->login($user),
            'back' => in_array('adjudicator', $user->role) ?
                '//' . config('app.creator_domain') :
                    ($user->level ? $this->redirectIfVerified :
                        $this->redirectTo)
        ]);
        */

        return redirect(in_array('adjudicator', $user->role) ?
        '//' . config('app.creator_domain') :
            ($user->level ? $this->redirectIfVerified :
                $this->redirectTo));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            //'promocode' => 'nullable|in:9617,7625'
        ], [], [
            'promocode' => __('«промокод»')
        ]);
    }

    /**
     * Send confirmation SMS
     *
     * @param  String phone
     * @return \App\User
     */
    protected function getCode($phone, $token = null)
    {
        $phone = substr('998' . preg_replace('/[^0-9]/', '', $phone), -12);

        if(empty(trim($phone))) {
            return ['result' => 'wrong_input'];
        }

        $user = User::where('phone', $phone)->first();

        if(!empty($user->password)) {
            return ['result' => 'used'];
        }

        $token_user = !empty($token) ? User::where('registration_token', $token)->first() : null;

        if($user && (!$token_user || $token_user->id != $user->id)) {
            $user->delete();
        }

        $user = $token_user;

        if(empty($user)) {
            $user = new User;
        }

        if(!$user->otp_sent_at || (new Carbon($user->otp_sent_at))->lessThan(Carbon::now()->subSeconds(env('SMS_CONFIRMATION_DELAY', 30)))) {
            $user->registration_token = sha1(str_random(80));
            $user->token_created_at = Carbon::now();
            $user->phone = $phone;

            $user->otp = mt_rand(10000, 99999);
            $user->otp_sent_at = Carbon::now();
            $user->save();

            if(!SMSSender::send($phone, $user->otp)) {
                return ['result' => 'sending_failed'];
            } else {
                return [
                    'result' => 'success',
                    'token' => $user->registration_token
                ];
            }
        } else {
            return ['result' => 'wait'];
        }
    }

    /**
     * Send confirmation SMS
     *
     * @return \App\User
     */
    protected function sendConfirmationCode(Request $request)
    {
        $token = $request->post('token');

        $data = ['phone' => preg_replace('/[^0-9]/', '', $request->post('phone'))];

        $validator = Validator::make($data, [
                'phone' => ['required', 'numeric', 'min:13', Rule::unique('users')->where(function ($query) {
                    return $query->whereNotNull('password');
                })]
            ], [
                'unique' => __('Данный номер телефона уже зарегистрирован в системе. Используйте форму восстановления пароля, если Вы забыли пароль.')
            ]
        );

        $data = $validator->validate();

        $user = User::where('phone', $data['phone'])->first();

        /*
        $token_user = !empty($token) ? User::where('registration_token', $token)->first() : null;

        if($user && (!$token_user || $token_user->id != $user->id)) {
            $user->delete();
        }

        $user = $token_user;
        */

        if(empty($user)) {
            $user = new User;
        }

        if(!$user->otp_sent_at || (new Carbon($user->otp_sent_at))->lessThan(Carbon::now()->subSeconds(env('SMS_CONFIRMATION_DELAY', 30)))) {
            $user->registration_token = sha1(str_random(80));
            $user->token_created_at = Carbon::now();
            $user->phone = $data['phone'];

            $user->otp = mt_rand(10000, 99999);
            $user->otp_sent_at = Carbon::now();
            $user->save();

            if(!SMSSender::send($data['phone'], $user->otp)) {
                $validator->getMessageBag()->add('phone', 'Произошла ошибка. Пожалуйста, попробуйте ещё раз.');
                return Redirect::back()->withErrors($validator)->withInput();
            } else {
                return redirect()->route('register-code')->withInput([
                    'phone' => $data['phone'],
                    'token' => $user->registration_token
                ]);
            }
        } else {
            $validator->getMessageBag()->add('phone', 'Вы можете отправить код через ' .
                (new Carbon($user->otp_sent_at))->diffInSeconds(Carbon::now()->subSeconds(env('SMS_CONFIRMATION_DELAY', 30))) . ' сек.');
            return redirect()->route('register')->withErrors($validator)->withInput();
        }
    }


    /**
     * Confirm phone
     *
     * @param  String token
     * @param  String code
     * @return \App\User
     */
    protected function confirmPhone($token, $code)
    {
        $user = User::where('registration_token', $token)
                    ->where('token_created_at', '>', Carbon::now()->subMinutes(10))
                    ->first();

        if(empty($user)) {
            return ['result' => 'wrong_token'];
        }

        if($user->otp == $code) {
            $user->phone_verified_at = Carbon::now();
            $user->save();

            return ['result' => 'success'];
        } else {
            return ['result' => 'wrong_code'];
        }

    }

    /**
     * Confirm phone
     *
     * @param  String token
     * @param  String code
     * @return \App\User
     */
    protected function attemptConfirmPhone(Request $request)
    {
        if($request->has('resend')) {
            return $this->sendConfirmationCode($request);
        }

        if($request->has('change')) {
            return redirect()->route('register')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'token' => 'nullable'
        ]);

        $user = User::where('registration_token', $request->post('token'))
                    ->where('token_created_at', '>', Carbon::now()->subMinutes(10))
                    ->first();

        if(empty($user)) {
            $validator->getMessageBag()->add('phone', 'Произошла ошибка. Пожалуйста, попробуйте ещё раз');
            return redirect()->route('register')->withErrors($validator)->withInput();
        }

        if($user->otp == $request->post('code')) {
            $user->phone_verified_at = Carbon::now();
            $user->save();

            return redirect()->route('register-password')->withInput([
                'token' => $user->registration_token
            ]);
        } else {
            $validator->getMessageBag()->add('code', 'Указан неверный код');
            return Redirect::back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Send confirmation e-mail
     *
     * @param  String email
     * @return \App\User
     */
    protected function verificationEmail($email, $token = null)
    {
        if(empty(trim($email))) {
            return ['result' => 'wrong_input'];
        }

        $user = User::where('email', $email)->first();

        if(!empty($user->password)) {
            return ['result' => 'used'];
        }

        $token_user = !empty($token) ? User::where('registration_token', $token)->first() : null;

        if($user && (!$token_user || $token_user->id != $user->id)) {
            $user->delete();
        }

        $user = $token_user;

        if(empty($user)) {
            $user = new User;
        }

        $user->registration_token = sha1(str_random(80));
        $user->token_created_at = Carbon::now();
        $user->email = $email;
        $user->save();

        UserVerification::generate($user);
        UserVerification::send($user);

        return [
            'result' => 'success',
            'token' => $user->registration_token
        ];
    }

    protected function checkEmail($token)
    {
        $user = User::where('registration_token', $token)->first();

        if($user && $user->hasVerifiedEmail()) {
            return ['result' => 'success'];
        }

        return ['result' => 'wrong'];
    }

    /**
     * Get the redirect path for a successful verification token verification.
     *
     * @return string
     */
    public function redirectAfterVerification()
    {
        $user = User::whereEmail(request()->input('email'))->first();

        if($user) {
            request()->session()->flash('_old_input.token', $user->registration_token);
        }

        return route('register'); //'email-verification.success'
    }

    /**
     * Show the verification success view.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailVerificationSuccess()
    {
        return view('auth.email-verification.success');
    }
}