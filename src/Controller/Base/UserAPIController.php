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
use App\Entity\Core\AbstractStoreFront;
use App\Service\JSONValidator;
use App\Voter\UserVoter;
use Doctrine\Common\Collections\ArrayCollection;
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
     */
    public function createUser(Request $request, JSONValidator $validator, UserPasswordEncoderInterface $encoder) {
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
        $result = $validator->validate($json, $constraint);
        if (200 == $result->getStatusCode()) {
            $user = new User();
            $user->setUsername($json["username"]);
            $user->setPassword($encoder->encodePassword($user, $json["password"]));
            $user->setFullName($json["fullName"]);
            $result = $validator->validateEntity($user);
            if (200 == $result->getStatusCode()) {
                $userGroup = $this->getDoctrine()->getRepository(SecurityGroup::class)->findOneBy([
                    "siteToken" => "ROLE_USER"
                ]);
                $userGroup->addChild($user);
                $em = $this->getDoctrine()->getManager();
                $this->denyAccessUnlessGranted(UserVoter::CREATE, $user);
                $em->persist($user);
                $em->persist($userGroup);
                $em->flush();
            }
        }
        return $result;
    }

    /**
     * Read a user with id without admin privilege
     * @Route("/api/users/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function readUser($id) {
        try {
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
                return new JsonResponse([
                    "status" => "failure",
                    "error" => "Entity cannot be located."
                ], 404);
            }
        } catch (AccessDeniedException $ex) {
            // Force 404 instead of 500
            return new JsonResponse([
                "status" => "failure",
                "error" => "Entity cannot be located."
            ], 404);
        }
    }

    /**
     * Query users id without admin privilege
     * @Route("/api/users", methods={"GET"})
     */
    public function queryUser(Request $request) {
        $querys = $request->query->all();
        $criteria = [];
        foreach ($querys as $k => $v) {
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
            return new JsonResponse([
                "status" => "failure",
                "error" => "Entity cannot be located."
            ], 404);
        }
    }

    /**
     * Update a user with id without admin privilege
     * Can only update self
     * @Route("/api/users/{id}", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function updateUser($id, Request $request, JSONValidator $validator, UserPasswordEncoderInterface $encoder) {
        $json = json_decode($request->getContent(), true);
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $this->denyAccessUnlessGranted(UserVoter::UPDATE, $user);
        if ($user) {
            $constraint = [
                "password" => [
                    new Assert\Optional(),
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
            $result = $validator->validate($json, $constraint);
            if (200 == $result->getStatusCode()) {
                $user->setFullName($json["fullName"]);
                if ($json["password"] ?? false) {
                    $user->setPassword($encoder->encodePassword($user, $json["password"]));
                }
                $result = $validator->validateEntity($user);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            return $result;
        }
        return new JsonResponse([
            "status" => "failure",
            "error" => "Entity cannot be located."
        ], 404);
    }
}