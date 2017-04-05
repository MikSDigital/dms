<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Assert\Assertion;
use AppBundle\Entity\Document;
use AppBundle\Entity\Tag;

/**
 * @author Florian Weber <florian.weber@fweber.info>
 */
final class DocumentController extends Controller
{
    public function collectionAction(Request $request)
    {
        $documents = $this->getDoctrine()->getRepository('AppBundle:Document')->findAll();
        $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->findAll();

        return $this->render('Document/collection.html.twig', [
            'documents' => $documents,
            'tags' => $tags,
        ]);
    }

    public function detailAction(Request $request, $hash)
    {
        $document = $this->getDoctrine()->getRepository('AppBundle:Document')->findOneByHash($hash);

        if(!$document) {
            throw new \Exception('Document not found!');
        }

        return $this->render('Document/detail.html.twig', [
            'document' => $document,
        ]);
    }

    public function showAction(Request $request, $hash)
    {
        $document = $this->getDoctrine()->getRepository('AppBundle:Document')->findOneByHash($hash);

        if(!$document) {
            throw new \Exception('Document not found!');
        }

        //prepare response
        $response = new Response(file_get_contents($document->getPath().'/'.$document->getFilename()));
        $response->headers->set('Content-Type', $document->getMimeType());

        //send
        return $response;
    }

    public function uploadAction(Request $request)
    {
        Assertion::notEmpty($request->get('title'), 'Title not specified');
        Assertion::notEmpty($request->get('tags'), 'Tags not specified');

        $file = $request->files->get('file');

        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $path = $this->getParameter('kernel.root_dir').'/../'.$this->getParameter('upload_directory');

        $document = new Document();
        $document
            ->setTitle($request->get('title'))
            ->setComment($request->get('comment'))
            ->setHash(hash_file('sha256', $file->getRealPath()))
            ->setPath($path)
            ->setFilename($fileName)
            ->setCreationDate(new \DateTime())
            ->setOriginalName($file->getClientOriginalName())
            ->setMimeType($file->getMimeType())
        ;

        $_tags = explode(',', $request->get('tags'));
        $tagCollection = new ArrayCollection();
        $em = $this->getDoctrine()->getEntityManager();

        foreach ($_tags as $_tag) {
            $tag = $this->getDoctrine()->getRepository('AppBundle:Tag')->findOneByTitle($_tag);

            //if tag is unknown create new
            if (!$tag) {
                $tag = new Tag();
                $tag
                    ->setTitle($_tag)
                ;

                $em->persist($tag);
            }

            $tagCollection->add($tag);
        }
        $em->flush();

        $document->setTags($tagCollection);

        $em->persist($document);

        $em->flush();

        $file->move(
            $path,
            $fileName
        );

        return $this->redirectToRoute('documents_collection');
    }

    public function deleteAction($hash)
    {
        $document = $this->getDoctrine()->getRepository('AppBundle:Document')->findOneByHash($hash);

        if(!$document) {
            throw new \Exception('Document not found!');
        }

        if(unlink($document->getPath().'/'.$document->getFilename())) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($document);
            $em->flush();
        }

        return $this->redirectToRoute('documents_collection');
    }
}
