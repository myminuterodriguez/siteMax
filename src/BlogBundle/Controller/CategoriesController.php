<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller.
 *
 * @Route("admin/categories")
 */
class CategoriesController extends Controller
{


    /**
     * Creates a new category entity.
     *
     * @Route("/{id}/new", name="admin_categories_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request,$id)
    {
        $category = new Categories();




        $form = $this->createForm('BlogBundle\Form\CategoriesType', $category);
        $form->handleRequest($request);
        $categories = $this->getDoctrine()->getRepository('BlogBundle:Categories')->findByProjet($id);
        if ($form->isSubmitted() && $form->isValid()) {

            //utilisation du service file_uploader
            $file = $category->getImageURL();
            $fileName = $this->get('app.file_uploader')->upload($file);
            $category->setImageURL($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush($category);

            return $this->redirectToRoute('projet_edit', array('id' => $category->getProjet()));
        }
        return $this->render(':admin/categories:new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
            'categories' => $categories,
        ));
    }

    /**
     * Finds and displays a category entity.
     *
     * @Route("/{id}", name="admin_categories_show")
     * @Method("GET")
     */
    public function showAction(Categories $category)
    {
        $deleteForm = $this->createDeleteForm($category);

        return $this->render('/admin/categories/show.html.twig', array(
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing category entity.
     *
     * @Route("/{id}/edit", name="admin_categories_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Categories $category)
    {
        //test existence fichier dans le répertoire et dans la base de donnée.
        $directory = $this->getParameter('img_directory');
        $this->get('app.file_uploader')->testFile($directory,$category);
        $file = $category->getImageURL();
        $deleteForm = $this->createDeleteForm($category);
        $editForm = $this->createForm('BlogBundle\Form\CategoriesType', $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            if (!null == $category->getImageURL()){
                $file = $category->getImageURL();
            }
            if($file !== '') {
                $fileName = $this->get('app.file_uploader')->upload($file);
                $category->setImageURL($fileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_categories_edit', array('id' => $category->getId()));
        }

        return $this->render(':admin/categories:edit.html.twig', array(
            'category' => $category,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a category entity.
     *
     * @Route("/{id}", name="admin_categories_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Categories $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);
        $id = $category->getProjet();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush($category);
        }

        return $this->redirectToRoute('projet_edit', array('id' => $id));
    }

    /**
     * Creates a form to delete a category entity.
     *
     * @param Categories $category The category entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Categories $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_categories_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}