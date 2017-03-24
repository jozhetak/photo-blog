<?php

namespace Api\V1\Http\Controllers;

use Api\V1\Http\Requests\CreateUser;
use Api\V1\Http\Requests\UpdateUser;
use Core\Models\User;
use Core\DataServices\User\Contracts\UserDataService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Class UsersController.
 *
 * @property UserDataService userDataService
 * @package Api\V1\Http\Controllers
 */
class UsersController extends ResourceController
{
    /**
     * UsersController constructor.
     *
     * @param Request $request
     * @param Guard $guard
     * @param string $presenterClass
     * @param UserDataService $userDataService
     */
    public function __construct(
        Request $request,
        Guard $guard,
        string $presenterClass,
        UserDataService $userDataService
    )
    {
        parent::__construct($request, $guard, $presenterClass);

        $this->userDataService = $userDataService;
    }

    /**
     * @apiVersion 1.0.0
     * @api {post} /v1/users Create
     * @apiName Create
     * @apiGroup Users
     * @apiHeader {String} Accept application/json
     * @apiHeader {String} Content-type application/json
     * @apiParam {String{1..255}} name User name.
     * @apiParam {String{1..255}} email User email address.
     * @apiParam {String{1..255}} password User password.
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 Created
     * {
     *     "id": 1,
     *     "name": "username",
     *     "email": "username@mail.address",
     *     "created_at": "2016-10-24 12:24:33",
     *     "updated_at": "2016-10-24 14:38:05",
     *     "role": "Customer"
     * }
     */

    /**
     * Create a user.
     *
     * @param CreateUser $request
     * @return User
     */
    public function create(CreateUser $request) : User
    {
        $user = new User;

        $user->setPassword($request->get('password'))
            ->generateApiToken()
            ->setCustomerRole();

        $this->userDataService->save($user, $request->all());

        return $user;
    }

    /**
     * @apiVersion 1.0.0
     * @api {get} /v1/users/:id Get
     * @apiName Get
     * @apiGroup Users
     * @apiHeader {String} Accept application/json
     * @apiParam {Integer{1..N}} :id Unique resource ID.
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 20O OK
     * {
     *     "id": 1,
     *     "name": "username",
     *     "email": "username@mail.address",
     *     "created_at": "2016-10-24 12:24:33",
     *     "updated_at": "2016-10-24 14:38:05",
     *     "role": "Customer"
     * }
     */

    /**
     * Get a user.
     *
     * @param User $user
     * @return User
     */
    public function get(User $user) : User
    {
        return $user;
    }

    /**
     * @apiVersion 1.0.0
     * @api {put} /v1/users/:id Update
     * @apiName Update
     * @apiGroup Users
     * @apiHeader {String} Accept application/json
     * @apiHeader {String} Content-type application/json
     * @apiParam {Integer{1..N}} :id Unique resource ID.
     * @apiParam {String{1..255}} name User name.
     * @apiParam {String{1..255}} email User email address.
     * @apiParam {String{1..255}} password User password.
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 20O OK
     * {
     *     "id": 1,
     *     "name": "username",
     *     "email": "username@mail.address",
     *     "created_at": "2016-10-24 12:24:33",
     *     "updated_at": "2016-10-24 14:38:05",
     *     "role": "Customer"
     * }
     */

    /**
     * Update a user.
     *
     * @param UpdateUser $request
     * @param User $user
     * @return User
     */
    public function update(UpdateUser $request, User $user) : User
    {
        if ($request->has('password')) {
            $user->setPassword($request->get('password'));
        }

        $this->userDataService->save($user, $request->all());

        return $user;
    }

    /**
     * @apiVersion 1.0.0
     * @api {delete} /v1/users/:id Delete
     * @apiName Delete
     * @apiGroup Users
     * @apiHeader {String} Accept application/json
     * @apiParam {Integer{1..N}} :id Unique resource ID.
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */

    /**
     * Delete a user.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user)
    {
        $this->userDataService->delete($user);
    }
}
