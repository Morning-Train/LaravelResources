<?php

namespace MorningTrain\Laravel\Resources\Operations\Auth;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use MorningTrain\Laravel\Resources\Support\Contracts\Operation;
use PermissionsService;

class Register extends Operation
{
    use RegistersUsers;

    const ROUTE_METHOD = 'post';

    protected $middlewares = ['guest'];

    public function handle($model_or_collection = null)
    {
        $request = request();

        $this->validator($request->all())->validate();

        $user = $this->createUser($request->all());

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data,
            [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function createUser(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        parent::registered($request, $user);

        return [
            'user' => $user,
            'csrf' => csrf_token(),
        ];
    }
}
