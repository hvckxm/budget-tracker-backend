<?php

namespace App\Controller;

use App\Entity\User;
use App\Request\RegistrationRequest;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepository
    ): JsonResponse {
        /** @var RegistrationRequest $dto */
        $dto = $serializer->deserialize($request->getContent(), RegistrationRequest::class, 'json');

        $errors = $validator->validate($dto);

        if ($errors->count() > 0) {
            throw new UnprocessableEntityHttpException((string) $errors);
        }

        $user = new User();
        $user->setUsername($dto->username);

        $password = $hasher->hashPassword($user, $dto->password);

        $user->setPassword($password);

        $userRepository->save($user, true);

        return $this->json([
            'message' => 'Successfully registered!',
        ]);
    }
}