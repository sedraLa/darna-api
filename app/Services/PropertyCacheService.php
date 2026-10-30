<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Models\Governorate;
use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PropertyCacheService
{
    private const TTL_FEATURED = 600;
    private const TTL_COUNTS   = 600;
    private const TTL_DETAIL   = 300;

    private const TAG_LISTINGS = 'property_listings';
    private const KEY_FEATURED = 'properties_featured';
    private const KEY_COUNTS   = 'properties_counts_by_governorate';

    private function detailTag(int $id): string
    {
        return 'property_detail_' . $id;
    }

    private function detailKey(int $id): string
    {
        return 'property_detail_' . $id;
    }

    private function isRedisException(\Throwable $e): bool
    {
        return $e instanceof \Predis\PredisException || (class_exists('RedisException') && $e instanceof \RedisException);
    }

    private function redis(): \Illuminate\Cache\Repository
    {
        ini_set('default_socket_timeout', 2);

        return Cache::store('redis');
    }

    public function getFeatured(): Collection
    {
        try {
            return $this->redis()
                ->tags([self::TAG_LISTINGS])
                ->remember(self::KEY_FEATURED, self::TTL_FEATURED, function () {
                    return $this->queryFeatured();
                });
        } catch (\Throwable $e) {
            if (! $this->isRedisException($e)) {
                throw $e;
            }

            Log::warning('Redis unavailable for getFeatured: ' . $e->getMessage());
            return $this->queryFeatured();
        }
    }

    public function getGovernoratePropertyCounts(): Collection
    {
        try {
            return $this->redis()
                ->tags([self::TAG_LISTINGS])
                ->remember(self::KEY_COUNTS, self::TTL_COUNTS, function () {
                    return $this->queryGovernoratePropertyCounts();
                });
        } catch (\Throwable $e) {
            if (! $this->isRedisException($e)) {
                throw $e;
            }

            Log::warning('Redis unavailable for getGovernoratePropertyCounts: ' . $e->getMessage());
            return $this->queryGovernoratePropertyCounts();
        }
    }

    public function getPropertyDetail(int $id, callable $loader): Property
    {
        try {
            return $this->redis()
                ->tags([$this->detailTag($id)])
                ->remember($this->detailKey($id), self::TTL_DETAIL, $loader);
        } catch (\Throwable $e) {
            if (! $this->isRedisException($e)) {
                throw $e;
            }

            Log::warning('Redis unavailable for getPropertyDetail ' . $id . ': ' . $e->getMessage());
            return $loader();
        }
    }

    public function invalidateAfterCreate(Property $property): void
    {
        $this->tryFlushListings();
    }

    public function invalidateAfterUpdate(Property $property): void
    {
        $this->tryFlushListings();
        $this->tryFlushDetail($property->id);
    }

    public function invalidateAfterDelete(Property $property): void
    {
        $this->tryFlushListings();
        $this->tryFlushDetail($property->id);
    }

    private function tryFlushListings(): void
    {
        try {
            $this->redis()->tags([self::TAG_LISTINGS])->flush();
        } catch (\Throwable $e) {
            if (! $this->isRedisException($e)) {
                throw $e;
            }

            Log::warning('Redis flush listings failed: ' . $e->getMessage());
        }
    }

    private function tryFlushDetail(int $id): void
    {
        try {
            $this->redis()->tags([$this->detailTag($id)])->flush();
        } catch (\Throwable $e) {
            if (! $this->isRedisException($e)) {
                throw $e;
            }

            Log::warning('Redis flush detail ' . $id . ' failed: ' . $e->getMessage());
        }
    }

    private function queryFeatured(): Collection
    {
        return Property::query()
            ->with(['governorate', 'images'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    private function queryGovernoratePropertyCounts(): Collection
    {
        return Governorate::query()
            ->withCount(['properties as available_properties_count' => function ($query) {
                $query->where('status', PropertyStatus::Available->value);
            }])
            ->orderByDesc('available_properties_count')
            ->get();
    }
}
