<?php

namespace Ocarat\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ocarat\UserBundle\Entity\User;
use Ocarat\UserBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $userManager = $this->container->get('fos_user.user_manager');

        $entities = $userManager->findUsers();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Template("OcaratCoreBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->findUserByEmail($entity->getEmail());
            $user->setPlainPassword($entity->getPassword());

            $role = $request->request->get('ocarat_userbundle_user')["role"];

            $user->setRoles(array($role));

            $userManager->updateUser($user, true);
            $this->get('session')->getFlashBag()->add('success', 'user.notif.create');
            return $this->redirect($this->generateUrl('user'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

        $choices = $this->get('ocarat.user.roles')->getRoles();

        $form->add('password', 'text', array('attr'=>array('class'=>'form-control'),'position' => array('after' => 'email')));
        $form->add('role', 'choice', array(
            'choices'   => $choices,
            'required'  => true,
            'attr' => array('class' => 'form-control'),
            'position' => array('after' => 'password'),
            'mapped' => false
        ));
        $form->add('submit', 'submit', array('label' => 'user.label.create', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $em->getRepository('OcaratUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }



        $editForm = $this->createEditForm($entity);

        $choices = $this->get('ocarat.user.roles')->getRoles();

        $editForm->add('role', 'choice', array(
            'choices'   => $choices,
            'required'  => true,
            'attr' => array('class' => 'form-control'),
            'position' => array('after' => 'password'),
            'mapped' => false
        ));

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        if($this->getUser()->hasRole("ROLE_SUPER_ADMIN")){

        $form->add('password', 'text', array('attr'=>array('class'=>'form-control'),'position' => array('after' => 'email')));

        }

        $form->add('submit', 'submit', array('label' => 'user.label.update', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Method("PUT")
     * @Template("OcaratCoreBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OcaratUserBundle:User')->find($id);

        $pass = $entity->getPassword();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);

        $choices = $this->get('ocarat.user.roles')->getRoles();

        $editForm->add('role', 'choice', array(
            'choices'   => $choices,
            'required'  => true,
            'attr' => array('class' => 'form-control'),
            'position' => array('after' => 'password'),
            'mapped' => false
        ));

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            if($this->getUser()->hasRole("ROLE_SUPER_ADMIN"))
            {
                $userManager = $this->get('fos_user.user_manager');
                $user = $userManager->findUserByEmail($entity->getEmail());
                if($pass != $entity->getPassword())
                    $user->setPlainPassword($entity->getPassword());
                $role = $request->request->get('ocarat_userbundle_user')["role"];
                $user->setRoles(array($role));
                $userManager->updateUser($user, true);
            }
            $this->get('session')->getFlashBag()->add('success', 'user.notif.edit');
            return $this->redirect($this->generateUrl('user'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OcaratUserBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }
            $this->get('session')->getFlashBag()->add('success', 'user.notif.delete');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'user.label.delete', 'attr' => array(
                'class' => 'btn btn-danger',
                'onclick' => 'return confirm(\''.$this->get('translator')->trans('user.label.really').'\')'
            )))
            ->getForm()
        ;
    }
}
