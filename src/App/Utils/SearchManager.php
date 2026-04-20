<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 13.03.16
 * Time: 22:57
 */

declare(strict_types=1);

namespace App\Utils;

use Doctrine\Persistence\ManagerRegistry;

class SearchManager
{

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function searchEstate(array $data): array
    {
        [$priceMin, $priceMax] = $this->resolvePriceRange($data['price']);
        $idDistrict = $data['district']?->getId();
        $idCategory = $data['category']->getId();
        $exceptFloor = $data['except_floor'];
        return $this->doctrine->getRepository(\App\Entity\Estate::class)
            ->findEstatesFromForm($idCategory, $idDistrict, $priceMin, $priceMax, $exceptFloor);
    }

    public function countEstate(array $data): int
    {
        [$priceMin, $priceMax] = $this->resolvePriceRange($data['price']);
        $idDistrict = $data['district']?->getId();
        $idCategory = $data['category']->getId();
        return $this->doctrine->getRepository(\App\Entity\Estate::class)
            ->countEstatesFromForm($idCategory, $idDistrict, $priceMin, $priceMax, $data['except_floor']);
    }

    public function searchEstatePaginated(array $data, int $page, int $limit): array
    {
        [$priceMin, $priceMax] = $this->resolvePriceRange($data['price']);
        $idDistrict = $data['district']?->getId();
        $idCategory = $data['category']->getId();
        return $this->doctrine->getRepository(\App\Entity\Estate::class)
            ->findEstatesFromFormPaginated($idCategory, $idDistrict, $priceMin, $priceMax, $data['except_floor'], $page, $limit);
    }

    private function resolvePriceRange(string $price): array
    {
        if ($price === 'more_then_50000') {
            return [50000, null];
        }
        if ($price === 'to_50000') {
            return [20001, 49999];
        }
        if ($price === 'to_20000') {
            return [0, 20001];
        }
        return [0, null];
    }
}