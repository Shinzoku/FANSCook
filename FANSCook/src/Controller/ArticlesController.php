<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/articles")
 */
class ArticlesController extends AbstractController
{
    /**
     * @Route("/new", name="articles_new", methods={"GET","POST"})
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */
    /**
     * @Route("/new", name="articles_new", methods={"GET","POST"})
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */

    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);
        $users = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($filename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $article->setImage($newFilename);
            $article->setUsers($users);
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('admin/articles/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="articles_show", methods={"GET"})
     * @param Articles $article
     * @return Response
     */
    public function show(Articles $article): Response
    {
        return $this->render('public/articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_articles_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Articles $article
     * @param SluggerInterface $slugger
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Articles $article, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->remove('users');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();
            if($file){
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($filename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }

            $this->getDoctrine()->getManager()->flush();
            $entityManager = $this->getDoctrine()->getManager();
            $article->setImage($newFilename)
                ->setDateUpdate(new \DateTime());
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('admin/articles/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="articles_delete", methods={"DELETE"})
     * @param Request $request
     * @param Articles $article
     * @return Response
     */
    public function delete(Request $request, Articles $article): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_articles_index');
    }
}
