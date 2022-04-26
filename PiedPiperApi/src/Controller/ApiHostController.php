<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Product;
use App\Entity\User;

class ApiHostController extends AbstractController
{
    //API IN EIGENES PROJEKT AUSLAGERN UND CALLS NUR NOCH AUF DIE API MACHEN

    #[Route('/api/products', name: 'app_api', methods: 'GET')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine
            ->getRepository(Product::class)
            ->findAll();

        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'teaser' => $product->getTeaser(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/products/add', name: 'app_api_add', methods: 'POST')]
    public function add(Request $request, ManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();

        $product = new Product();
        $product->setName($request->request->get('name'));
        $product->setTeaser($request->request->get('teaser'));
        $product->setDescription($request->request->get('description'));
        $product->setImage($request->request->get('image'));
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json("Product added", 200);
    }

    #[Route('/api/products/show/{id}', name: 'app_api_show', methods: 'GET')]
    public function show(int $id, ManagerRegistry $doctrine):Response {
        $product = $doctrine
            ->getRepository(Product::class)
            ->find($id);

        if(!$product) {
            return $this->json("Could not find the product", 404);
        } else {
            $data = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'teaser' => $product->getTeaser(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
            ];

            return $this->json($data, 200);
        }
    }

    #[Route('/api/products/edit/', name: 'app_api_edit', methods: 'PUT')]
    public function edit(Request $request, ManagerRegistry $doctrine):Response {
        $entityManager = $doctrine->getManager();
        $id = $request->request->get('id');
        $product = $entityManager->getRepository(Product::class)->find($id);

        if(!$product) {
            return $this->json("Could not find product to edit!", 404);
        } else {
            $product->setName($request->request->get('name'));
            $product->setTeaser($request->request->get('teaser'));
            $product->setDescription($request->request->get('description'));
            $product->setImage($request->request->get('image'));

            $entityManager->flush();

            $data = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'teaser' => $product->getTeaser(),
                'description' => $product->getDescription(),
                'image' => $product->getImage(),
            ];

            return $this->json($data, 200);
        }
    }

    #[Route('/api/products/delete/{id}', name: 'app_api_delete', methods: 'DELETE')]
    public function delete(int $id, ManagerRegistry $doctrine):Response {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if(!$product) {
            return $this->json("Product not found", 404);
        } else {
            $entityManager->remove($product);
            $entityManager->flush();
            return $this->json("Product was removed!", 200);
        }
    }

    #[Route('/api/user', name: 'app_api_user', methods: 'GET')]
    public function user(ManagerRegistry $doctrine): Response {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)
            ->findAll();

        $data = [];

        foreach ($user as $singleUser) {
            $data[] = [
                'id' => $singleUser->getId(),
                'name' => $singleUser->getName(),
                'email' => $singleUser->getEmail(),
            ];
        }

        return $this->json($data);
    }
}
