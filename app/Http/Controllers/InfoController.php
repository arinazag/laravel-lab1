<?php

namespace App\Http\Controllers;

use App\DTO\ServerInfoDTO;
use App\DTO\ClientInfoDTO;
use App\DTO\DatabaseInfoDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfoController extends Controller
{
    /**
     * Возвращает информацию о сервере и PHP
     */
    public function serverInfo()
    {
        $serverInfo = new ServerInfoDTO(
            phpVersion: PHP_VERSION,
            serverSoftware: $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            phpSapi: php_sapi_name(),
            maxExecutionTime: (int) ini_get('max_execution_time'),
            memoryLimit: (int) ini_get('memory_limit')
        );

        return response()->json($serverInfo->toArray());
    }

    /**
     * Возвращает информацию о клиенте
     */
    public function clientInfo(Request $request)
    {
        $clientInfo = new ClientInfoDTO(
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            requestMethod: $request->method(),
            requestUri: $request->path()
        );

        return response()->json($clientInfo->toArray());
    }

    /**
     * Возвращает информацию о базе данных
     */
    public function databaseInfo()
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.$connection.driver");
            $databaseName = config("database.connections.$connection.database");
            
            // Для PostgreSQL
            $versionInfo = DB::select('SHOW server_version');
            $serverVersion = $versionInfo[0]->server_version ?? 'Unknown';
            
        } catch (\Exception $e) {
            $serverVersion = 'Unable to get version';
            $databaseName = 'Unable to get database name';
        }

        $databaseInfo = new DatabaseInfoDTO(
            connection: $connection,
            driver: $driver,
            databaseName: $databaseName,
            serverVersion: $serverVersion
        );

        return response()->json($databaseInfo->toArray());
    }
}