<?php

namespace Tests\Helpers; // Corrected namespace

class TestHelpers
{
    /**
     * Returns the absolute path to a mock file.
     *
     * @param string $mockFileName The name of the mock file (e.g., 'AllPropertiesResponse.xml').
     * @param string $apiType The type of API (e.g., 'BookingManager', 'Cubilis').
     * @return string The absolute path to the mock file.
     * @throws \InvalidArgumentException If the mock file does not exist.
     */
    public static function getMockFilePath(string $mockFileName, string $apiType = 'BookingManager'): string
    {
        $projectRoot = dirname(dirname(__DIR__)); // Corrected to point to project root
        $filePath = $projectRoot . '/mocks/' . strtolower($apiType) . '/' . $mockFileName;

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Mock file not found at path: {$filePath}");
        }
        return $filePath;
    }
}
