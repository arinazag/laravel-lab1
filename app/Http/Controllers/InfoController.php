    public function databaseInfo()
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.$connection.driver");
            $databaseName = config("database.connections.$connection.database");
            
            // Для MySQL
            $versionInfo = DB::select('SELECT VERSION() as version');
            $serverVersion = $versionInfo[0]->version ?? 'Unknown';
            
        } catch (\Exception $e) {
            $serverVersion = 'Unable to get version: ' . $e->getMessage();
            $databaseName = 'Unable to get database name';
        }

        $databaseInfo = new DatabaseInfoDTO(
            connection: $connection,
            driver: $driver,
            databaseName: $databaseName,
            serverVersion: $serverVersion
        );

        return response()->json($databaseInfo);
    }