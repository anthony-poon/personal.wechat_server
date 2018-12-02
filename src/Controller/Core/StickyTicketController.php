<?php
/**
 * Created by PhpStorm.
 * User: ypoon
 * Date: 2/12/2018
 * Time: 1:52 PM
 */

namespace App\Controller\Core;

use App\Entity\Core\StickyTicket;
use App\FormType\Form\Core\StickyTicketForm;
use App\Service\EntityTableHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StickyTicketController extends Controller {

    /**
     * @Route("/admin/sticky-ticket", name="sticky_ticket_list_tickets")
     */
    public function listTickets(EntityTableHelper $helper) {
        $repo = $this->getDoctrine()->getRepository(StickyTicket::class);
        $tickets = $repo->findAll();
        $helper->addButton("Create", "sticky_ticket_create_ticket");
        $helper->addButton("Edit", "sticky_ticket_edit_ticket");
        $helper->setHeader([
            "#",
            "Code",
            "Consumed",
            "Create",
            "Expire"
        ]);
        $helper->setTitle("Sticky Tickets");
        foreach ($tickets as $ticket) {
            /* @var StickyTicket $ticket */
            $helper->addRow($ticket->getId(), [
                $ticket->getId(),
                $ticket->getCode(),
                $ticket->isConsumed()? "True" : "False",
                $ticket->getCreateDate()->format("Y-m-d"),
                $ticket->getExpireDate()->format("Y-m-d")
            ]);
        }
        return $this->render("render/entity_table.html.twig",
            $helper->compile()
        );
    }

    /**
     * @Route("/admin/sticky-ticket/create", name="sticky_ticket_create_ticket")
     */
    public function createTicket() {
        $ticket = new StickyTicket();
        $em = $this->getDoctrine()->getManager();
        $em->persist($ticket);
        $em->flush();
        return $this->redirectToRoute("sticky_ticket_edit_ticket", [
            "id" => $ticket->getId()
        ]);
    }
    /**
     * @Route("/admin/sticky-ticket/{id}", name="sticky_ticket_edit_ticket")
     */
    public function editTicket(int $id, Request $request) {
        $repo = $this->getDoctrine()->getRepository(StickyTicket::class);
        $ticket = $repo->find($id);
        $form = $this->createForm(StickyTicketForm::class, $ticket);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $storeItem = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($storeItem);
            $em->flush();
            return $this->redirectToRoute("sticky_ticket_list_tickets");
        }
        return $this->render("render/simple_form.html.twig", [
            "title" => "Sticky Ticket",
            "form" => $form->createView(),
        ]);

    }
}