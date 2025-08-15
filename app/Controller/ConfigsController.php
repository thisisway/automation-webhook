<?php

namespace App\Controller;

use App\Models\Configs;
use Kernel\Request;

class ConfigsController extends Controller
{
    public function setDomain(Request $request) {
        $domain = $request->get('domain');
        $domain = strtolower(trim(str_replace(['http','https',':','/'], '', $domain)));

        // Validate if domain is valid
        if (!$domain || !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain)) {
            return $this->json(['error' => true, 'message' => 'Invalid domain']);
        }

        // Check if the domain is online
        $isOnline = false;

        // First check DNS records
        if (checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) {
            // Try to establish a connection to the domain on port 80 (HTTP)
            $connection = @fsockopen($domain, 80, $errno, $errstr, 5);
            if ($connection) {
                $isOnline = true;
                fclose($connection);
            } else {
                // Try port 443 (HTTPS) if port 80 fails
                $connection = @fsockopen('ssl://' . $domain, 443, $errno, $errstr, 5);
                if ($connection) {
                    $isOnline = true;
                    fclose($connection);
                }
            }
        }

        // Return error if domain is not online
        if (!$isOnline) {
            return $this->json(['error' => true, 'message' => 'Domain is not reachable or online']);
        }
        
        $domain = (new Configs)->where('key', 'domain')->first();
        $domain->value = $domain;
        $domain->save();

        // Logic to set the domain
        return $this->json(['error' => false, 'message' => 'Domain set successfully']);
    }
}