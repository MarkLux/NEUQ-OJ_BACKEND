<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-21
 * Time: 下午3:33
 */

namespace NEUQOJ\Services;


use NEUQOJ\Exceptions\Problem\SolutionNotExistException;
use NEUQOJ\Jobs\SendJugdeRequest;
use NEUQOJ\Repository\Eloquent\CompileInfoRepository;
use NEUQOJ\Repository\Eloquent\RuntimeInfoRepository;
use NEUQOJ\Repository\Eloquent\SolutionRepository;
use NEUQOJ\Repository\Eloquent\SourceCodeRepository;
use NEUQOJ\Services\Contracts\SolutionServiceInterface;

class SolutionService
{
    private $solutionRepo;
    private $sourceCodeRepo;
    private $cacheService;

    public function __construct(
        SolutionRepository $solutionRepository, SourceCodeRepository $sourceCodeRepository,
        CacheService $cacheService
    )
    {
        $this->solutionRepo = $solutionRepository;
        $this->sourceCodeRepo = $sourceCodeRepository;
        $this->cacheService = $cacheService;
    }

    public function getAllSolutions(int $page, int $size, array $condition)
    {
        return $this->solutionRepo->getAllSolutions($page, $size, $condition);
    }

    public function getSolution(int $solutionId)
    {
        return $this->solutionRepo->getSolution($solutionId)->first();
    }

    public function getRawSolution(int $solutionId)
    {
        // 不join其他信息
        return $this->solutionRepo->get($solutionId)->first();
    }

    public function getSolutionBy(string $param, $value, array $columns = ['*'])
    {
        return $this->solutionRepo->getBy($param, $value, $columns);
    }

    public function getSolutionCount(): int
    {
        return $this->solutionRepo->getTotalCount();
    }

    public function getSourceCode(int $solutionId)
    {
        return $this->sourceCodeRepo->get($solutionId, ['source', 'private', 'created_at'], 'solution_id')->first();
    }

    public function isUserAc(int $userId, int $problemId): bool
    {
        $count = $this->solutionRepo->getWhereCount([
            'user_id' => $userId,
            'problem_id' => $problemId,
            'result' => 4
        ]);

        if ($count <= 1) {
            return false;
        } else {
            return true;
        }
    }

    public function isSolutionExist(int $solutionId): bool
    {
        $solution = $this->solutionRepo->get($solutionId, ['id']);

        if ($solution == null)
            return false;
        return true;
    }

    public function getSolutionStatistics(int $day)
    {
        if (!$this->cacheService->isCacheExist(CacheService::$STATISTIC_KEY)) {
            // 缓存过期，重新设置
            $stat = $this->solutionRepo->getSolutionStatistics($day);
            $submits = [
                'day' => $this->solutionRepo->getTodaySubmits(),
                'week' => $this->solutionRepo->getThisWeekSubmits(),
                'month' => $this->solutionRepo->getThisMonthSubmits()
            ];
            $data = [
                'statistics' => $stat,
                'submits' => $submits
            ];
            $this->cacheService->setStatisticCache($data,3600);
            return $data;
        }else {
            return $this->cacheService->getStatisticCache();
        }
    }
}