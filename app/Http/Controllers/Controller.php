<?php

namespace App\Http\Controllers;

use App\Exceptions\UploadException;
use App\Http\Result;
use App\Services\ImageService;
use App\Utils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, Result;

    public function upload(Request $request, ImageService $service): Response
    {
        try {
            $image = $service->store($request);
        } catch (UploadException $e) {
            return $this->fail($e->getMessage());
        } catch (Throwable $e) {
            Utils::e($e, 'Web 上传文件时发生异常');

            return $this->fail('服务暂时不可用，请稍后重试')->setStatusCode(500);
        }

        return $this->success('上传成功', $image->setAppends(['pathname', 'links'])->only(
            'id', 'pathname', 'origin_name', 'size', 'mimetype', 'md5', 'sha1', 'links'
        ));
    }
}
