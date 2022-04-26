<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Product;

class ProductsController extends AbstractController
{
    private HttpClientInterface $client;
    private string $api_url = 'http://127.0.0.1:8001/api/';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    //List the Products in the Backend
    #[Route('/products', name: 'app_products')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $response = $this->client->request(
            'GET',
            $this->api_url.'products'
        );

        $products = json_decode($response->getContent(), true);

        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
        ]);
    }

    #[Route('products/show/{id}', name: 'app_products_showsingle')]
    public function showSingleProduct(int $id = null): Response {
        if($id == null) {
            $this->redirectToRoute('app_products');
        } else {
            $response = $this->client->request(
                'GET',
                $this->api_url.'products/show/'.$id,
            );

            if($response->getStatusCode() == 200) {
                $product = json_decode($response->getContent(), true);

                return $this->render('products/showsingleproduct.html.twig', [
                   'product' => $product,
                ]);
            } else {
                $this->redirectToRoute('app_products');
            }
        }
    }

    //build a form for adding products to the database
    #[Route('/products/add', name: 'app_products_add')]
    public function addProducts(Request $request,
                                EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $result = "";

        $product = new Product();
        $form = $this->createForm(FormType::class, $product);
        $form->add('name', TextType::class, [ 'data' => '', 'attr' => array(
            'placeholder' => 'Name'
        )]);
        $form->add('teaser', TextareaType::class, [ 'data' => '', 'attr' => array(
            'placeholder' => 'Teaser',
            'label' => false
        )]);
        $form->add('description', TextareaType::class, [ 'data' => '', 'attr' => array(
            'placeholder' => 'Long Description'
        )]);
        $form->add('image', TextType::class, [ 'data' => '', 'attr' => array(
            'placeholder' => 'Image'
        )]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            //CALL THE API! IN THE FUTURE
            $response = $this->client->request(
                'POST',
                $this->api_url.'products/add',
                [
                    'body' => [
                        'name' => $product->getName(),
                        'teaser' => $product->getTeaser(),
                        'description' => $product->getDescription(),
                        'image' => $product->getImage(),
                    ],
                ],
            );

            if($response->getStatusCode() != 200) {
                $result = "There was a Problem adding the product. Status: 
                    ".$response->getStatusCode();
            } else {
                $result = "Product added";
            }
        }

        return $this->render('products/addproduct.html.twig', [
           'productForm' => $form->createView(),
            'result' => $result,
        ]);
    }

    //build a form for adding products to the database
    #[Route('/products/edit/{id}', name: 'app_products_edit')]
    public function editProduct(Request $request,
                                EntityManagerInterface $entityManager, int $id = null): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $result = "";

        if($id == null) {
            //List all Products before editing
            $response = $this->client->request(
                'GET',
                $this->api_url."products/"
            );

            $products = json_decode($response->getContent(), true);
            return $this->render('products/editproducts.html.twig', [
                'products' => $products,
            ]);

        } else {
            $response = $this->client->request(
                'GET',
                $this->api_url.'products/show/'.$id
            );

            if($response->getStatusCode() == 200) {

                $product = json_decode($response->getContent(), true);
                $form = $this->createForm(FormType::class, $product);
                $form->add('name', TextType::class, [ 'data' => $product["name"]]);
                $form->add('teaser', TextareaType::class, [ 'data' => $product["teaser"]]);
                $form->add('description', TextareaType::class, [ 'data' => $product["description"]]);
                $form->add('image', TextType::class, [ 'data' => $product["image"]]);
                $form->add('id', HiddenType::class, ['data' => $product['id']]);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()) {
                    //CALL THE API! IN THE FUTURE
                    $response = $this->client->request(
                        'PUT',
                        $this->api_url.'products/edit/',
                        [
                            'body' => [
                                'id' => $product["id"],
                                'name' => $form->get('name')->getData(),
                                'teaser' => $form->get('teaser')->getData(),
                                'description' => $form->get('description')->getData(),
                                'image' => $form->get('image')->getData(),
                            ],
                        ],
                    );

                    return $this->redirectToRoute('app_products');

                } else {

                    return $this->render('products/editsingleproduct.html.twig', [
                        'productForm' => $form->createView(),
                        'result' => $result,
                    ]);
                }


            } else {
                return $this->redirectToRoute('app_products_edit');
            }
        }
    }

    #[Route('/products/delete/{id}', name: 'app_products_delete')]
    public function deleteProduct(int $id = null): Response {

        if($id != null) {
            $response = $this->client->request(
                'DELETE',
                $this->api_url.'products/delete/'.$id
            );
            if($response->getStatusCode() == 200) {
                $message = "Product deleted! Status: ".$response->getStatusCode();
            } else {
                $message = "Product not found! Status: ".$response->getStatusCode();
            }
        } else {
            $message = "No product given";
        }

        $response = $this->client->request(
            'GET',
            $this->api_url.'products'
        );

        $products = json_decode($response->getContent(), true);

        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
            'message' => $message,
        ]);
    }

}
