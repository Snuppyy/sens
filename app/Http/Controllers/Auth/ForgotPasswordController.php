<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Lib\SMSSender;
use App\User;
use Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (strpos($request->email, '@') !== false) {
            $this->validateEmail($request);

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $response = $this->broker()->sendResetLink(
                $request->only('email')
            );
        } else {
            $data = ['email' => preg_replace('/[^0-9]/', '', $request->email)];

            $validator = Validator::make($data, ['email' => ['required', 'numeric', 'min:13', 'exists:users,phone']], [
                    'exists' => __('Данный номер телефона не зарегистрирован в системе')
                ]
            );

            $data = $validator->validate();

            $user = User::where('phone', $data['email'])->first();

            if(!$user->otp_sent_at || (new Carbon($user->otp_sent_at))->lessThan(Carbon::now()->subSeconds(env('SMS_CONFIRMATION_DELAY', 30)))) {
                $user->otp = mt_rand(10000, 99999);
                $user->otp_sent_at = Carbon::now();
                $user->save();

                if(!SMSSender::send($data['email'], $user->otp)) {
                    return redirect()->back()->withErrors(['email' => 'Возникла какая-то проблема при отправке сообщения']);
                } else {
                    return redirect()->route('password.otp', ['user' => $user->id]);
                }
            } else {
                return redirect()->back()->withErrors(['email' => 'Сообщение уже было отправлено. Пожалуйста, подождите и попробуйте ещё раз.']);
            }
        }

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Display the form to request password reset via OTP.
     *
     * @return \Illuminate\Http\Response
     */
    public function showOtpForm(User $user)
    {
        return view('auth.passwords.otp', ['user' => $user->id]);
    }

    /**
     * Chack OTP and redirect to the password reset form.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkOtp(Request $request, User $user)
    {
        if (!$user->otp || $user->otp != $request->otp || !$user->otp_sent_at ||
            (new Carbon($user->otp_sent_at))->lessThan(Carbon::now()->subSeconds(env('OTP_TIMEOUT', 300)))
        ) {
            return redirect()->back()->withErrors(['otp' => 'Неправильный либо устаревший код']);
        }

        if (!$user->email) {
            $user->email = $user->phone;
            $user->save();
        }

        $token = app('auth.password.broker')->createToken($user);

        return redirect()->route('password.reset', ['token' => $token, 'email' => $user->email]);
    }
}
