<?php

namespace Benjamin\AyonConnector\Contracts;

use Benjamin\AyonConnector\Exceptions\AyonSyncException;
use Illuminate\Http\Client\Response;

interface AyonClientInterface
{
    /**
     * @param array<string, mixed> $userData
     * @param string $ayonName
     * @return Response
     * @throws AyonSyncException
     */
    public function createUser(array $userData, string $ayonName): Response;

    /**
     * @param array<string, mixed> $userData
     * @param string $ayonName
     * @return Response
     * @throws AyonSyncException
     */
    public function updateUser(array $userData, string $ayonName): Response;

    /**
     * @param string $ayonName
     * @return Response
     * @throws AyonSyncException
     */
    public function deleteUser(string $ayonName): Response;

    /**
     * @param string $ayonName
     * @return Response
     * @throws AyonSyncException
     */
    public function deactivateUser(string $ayonName): Response;
}
