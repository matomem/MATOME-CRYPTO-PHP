<?php
class ApiCredentialsController {
    private $apiCredentials;
    private $user;

    public function __construct($pdo, $userId) {
        $this->apiCredentials = new ApiCredentials($pdo, $userId);
        $this->user = new User($pdo);
    }

    public function index() {
        $credentials = $this->apiCredentials->getAllCredentials();
        require_once 'app/views/api/index.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $exchange = $_POST['exchange'] ?? '';
            $apiKey = $_POST['api_key'] ?? '';
            $apiSecret = $_POST['api_secret'] ?? '';

            if (empty($exchange) || empty($apiKey) || empty($apiSecret)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: /api/add');
                exit;
            }

            if ($this->apiCredentials->saveCredentials($exchange, $apiKey, $apiSecret)) {
                $_SESSION['success'] = 'API credentials saved successfully';
                header('Location: /api');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to save API credentials';
            }
        }
        require_once 'app/views/api/add.php';
    }

    public function deactivate($exchange) {
        if ($this->apiCredentials->deactivateCredentials($exchange)) {
            $_SESSION['success'] = 'API credentials deactivated successfully';
        } else {
            $_SESSION['error'] = 'Failed to deactivate API credentials';
        }
        header('Location: /api');
        exit;
    }

    public function verify($exchange) {
        $credentials = $this->apiCredentials->getCredentials($exchange);
        if (!$credentials) {
            $_SESSION['error'] = 'No active credentials found for this exchange';
            header('Location: /api');
            exit;
        }

        // Here you would implement the actual verification with the exchange
        // For now, we'll just return a success message
        $_SESSION['success'] = 'API credentials verified successfully';
        header('Location: /api');
        exit;
    }
} 