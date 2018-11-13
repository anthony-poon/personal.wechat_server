<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 1/11/2018
 * Time: 10:02 AM
 */

namespace App\Controller\Base;

use App\Entity\Base\SecurityGroup;
use App\Entity\Base\User;
use App\Service\JSONValidator;
use App\Exception\ValidationException;
use App\Voter\UserVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints as Assert;

class UserAPIController extends Controller {
    /**
     * Register a new user without admin privilege
     * @Route("/api/users", methods={"POST"})
     * @throws ValidationException
     */
    public function createUser(Request $request, JsonValidator $validator, UserPasswordEncoderInterface $encoder) {
        $json = json_decode($request->getContent(), true);
        $this->denyAccessUnlessGranted(UserVoter::CREATE);
        $constraint = [
            "username" => [
                new Assert\NotBlank(),
                new Assert\Regex("/^[\w_\-\. ]+$/"),
                new Assert\Length([
                    "min" => 5,
                    "max" => 50
                ])
            ],
            "password" => [
                new Assert\NotBlank(),
                new Assert\Length([
                    "min" => 5,
                    "max" => 50
                ])
            ],
            "fullName" => [
                new Assert\NotBlank(),
                new Assert\Regex("/^[\w_\-\. ]+$/u"),
            ]
        ];
        $validator->validate($json, $constraint);
        $user = new User();
        $user->setUsername($json["username"]);
        $user->setPassword($encoder->encodePassword($user, $json["password"]));
        $user->setFullName($json["fullName"]);
        $validator->validateEntity($user);
        $userGroup = $this->getDoctrine()->getRepository(SecurityGroup::class)->findOneBy([
            "siteToken" => "ROLE_USER"
        ]);
        $userGroup->addChild($user);
        $em = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted(UserVoter::CREATE, $user);
        $em->persist($user);
        $em->persist($userGroup);
        $em->flush();
        return new JsonResponse([
            "status" => "success",
            "user" => [
                "id" => $user->getId(),
                "fullName" => $user->getFullName(),
            ]
        ]);
    }

    /**
     * Read a user with id without admin privilege
     * @Route("/api/users/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function readUser($id) {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            "id" => $id,
        ]);
        $this->denyAccessUnlessGranted(UserVoter::READ, $user);
        if ($user) {
            return new JsonResponse([
                "status" => "success",
                "user" => [
                    "id" => $user->getId(),
                    "fullName" => $user->getFullName(),
                ]
            ]);
        } else {
            throw new NotFoundHttpException("Entity cannot be located.");
        }
    }

    /**
     * Query users id without admin privilege
     * @Route("/api/users", methods={"GET"})
     */
    public function queryUser(Request $request) {
        $queries = $request->query->all();
        $criteria = [];
        foreach ($queries as $k => $v) {
            if ($v) {
                switch ($k) {
                    case "id":
                    case "fullName":
                        $criteria[$k] = $v;
                        break;
                }
            }
        }
        $users = $this->getDoctrine()->getRepository(User::class)->findBy($criteria);
        $rtn = [
            "status" => "success",
            "users" => []
        ];
        foreach ($users as $user) {
            try {
                $this->denyAccessUnlessGranted(UserVoter::READ, $user);
                $rtn["users"][] = [
                    "id" => $user->getId(),
                    "fullName" => $user->getFullName(),
                ];
            } catch (AccessDeniedException $ex) {
                // Don't need to throw, just skip
            }
        }
        if (0 !== count($rtn["users"])) {
            return new JsonResponse($rtn);
        } else {
            throw new NotFoundHttpException("Entity cannot be located.");
        }
    }

    /**
     * Update a user with id without admin privilege
     * Can only update self
     * @Route("/api/users/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     * @throws ValidationException
     * @throws NotFoundHttpException
     */
    public function updateUser($id, Request $request, JsonValidator $validator, UserPasswordEncoderInterface $encoder) {
        $json = json_decode($request->getContent(), true);
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $this->denyAccessUnlessGranted(UserVoter::UPDATE, $user);
        if ($user) {
            $constraint = [
                "password" => [
                    new Assert\Optional([
                        new Assert\Length([
                            "min" => 5,
                            "max" => 50
                        ])
                    ]),

                ],
                "fullName" => [
                    new Assert\NotBlank(),
                    new Assert\Regex("/^[\w_\-\. ]+$/u"),
                ]
            ];
            $validator->validate($json, $constraint);
            $user->setFullName($json["fullName"]);
            if ($json["password"] ?? false) {
                $user->setPassword($encoder->encodePassword($user, $json["password"]));
            }
            $validator->validateEntity($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new JsonResponse([
                "status" => "success",
                "user" => [
                    "id" => $user->getId(),
                    "fullName" => $user->getFullName(),
                ]
            ]);
        }
        throw new NotFoundHttpException("Entity cannot be located.");
    }
}