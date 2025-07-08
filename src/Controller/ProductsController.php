<?php
namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressForm;
use App\Service\Product\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'product_index', methods: ['GET'])]
    public function index(Request $request, ProductService $productService): Response
    {
        $page     = max(1, $request->query->getInt('page', 1));
        $limit    = 12;
        $category = $request->query->get('category');
        $name     = $request->query->get('name');

        $result = $productService->getPaginatedProducts($page, $limit, $category, $name);

        return $this->render('products/index.html.twig', [
            'products' => $result['items'],
            'page'     => $page,
            'limit'    => $limit,
            'total'    => $result['total'],
            'category' => $category,
            'name'     => $name,
        ]);
    }

    #[Route('/products/{id}', name: 'show_product', methods: ['GET'])]
    public function show(ProductService $productService, string $id): Response
    {
        $products = $productService->getAllApi();

        $product = null;
        foreach ($products as $item) {
            if ($item['id'] === $id) {
                $product = $item;
                break;
            }
        }

        return $this->render("Products/show.html.twig", [
            "product" => $product
        ]);
    }

}
