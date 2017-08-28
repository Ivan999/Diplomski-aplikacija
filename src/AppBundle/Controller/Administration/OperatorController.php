<?php
namespace AppBundle\Controller\Administration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Model\Administration\Operator as FormOperator;
use AppBundle\Form\Type\Administration\OperatorType;
use IssueInvoices\Domain\Model\Administration\Operator;

class OperatorController extends Controller
{
    /**
     * @Route("/administration/operators", name="AppBundle_Administration_operators")
     */
    public function operatorsAction(Request $request)
    {
        $userAdministration = $this->getUser()->getAdministration();
        $userAdministrationId = $userAdministration->getId();
        // Dohvatiti sve kupce iz repozitorija i prikazati
        $operators = $this->get('app.operator_repository')->findAllForUserAdministration($userAdministrationId);

        return $this->render('AppBundle:Administration:operators.html.twig', [
            'operators' => $operators
        ]);
    }

    /**
     * @Route("/administration/operator/add", name="AppBundle_Administration_addOperator")
     */
    public function addOperatorAction(Request $request)
    {
        $formOperator = new FormOperator();
        $form = $this->createForm(OperatorType::class, $formOperator);

        $form->handleRequest($request);

        // Dohvatiti administraciju prijavljenog korisnika
        $userAdministration = $this->getUser()->getAdministration();

        if ($form->isSubmitted() && $form->isValid()) {
            $formOperator = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();

            $operator = new Operator();
            $operator->setName($formOperator->name);
            $operator->setOib($formOperator->oib);
            $operator->setLabel($formOperator->label);

            $userAdministration->addOperator($operator);
            $entityManager->persist($userAdministration);
            $entityManager->flush();

            return $this->redirectToRoute('AppBundle_Administration_operators');
        }

        return $this->render('AppBundle:Administration:addOperator.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/administration/operator/edit/{operatorId}", name="AppBundle_Administration_editOperator")
     */
    public function editOperatorAction(Request $request, int $operatorId)
    {
        $formOperator = new FormOperator();

        // Nađi poslovni prostor za poslani slug u ruti
        $operator = $this->get('app.operator_repository')->find($operatorId);

        $formOperator->name = $operator->getName();
        $formOperator->oib = $operator->getOib();
        $formOperator->label = $operator->getLabel();

        $form = $this->createForm(OperatorType::class, $formOperator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formOperator = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            
            $operator->setName($formOperator->name);
            $operator->setOib($formOperator->oib);
            $operator->setLabel($formOperator->label);

            $entityManager->persist($operator);
            $entityManager->flush();

            return $this->redirectToRoute('AppBundle_Administration_operators'); 
        }

        return $this->render('AppBundle:Administration:addOperator.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/administration/operator/delete/{operatorId}", name="AppBundle_Administration_deleteOperator")
     */
    public function deleteOperatorAction(int $operatorId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $operator = $this->get('app.operator_repository')->findOneById($operatorId);

        $entityManager->remove($operator);
        $entityManager->flush();

        return $this->redirectToRoute('AppBundle_Administration_operators');
    }
}