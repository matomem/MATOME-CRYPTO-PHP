<?php
class PriceAlertController {
    private $priceAlert;
    private $wallet;

    public function __construct($pdo, $userId, $luno) {
        $this->priceAlert = new PriceAlert($pdo, $userId);
        $this->wallet = new Wallet($luno, $pdo, $userId);
    }

    public function index() {
        $alerts = $this->priceAlert->getAllAlerts();
        require_once 'app/views/alerts/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $baseCurrency = $_POST['base_currency'] ?? '';
            $quoteCurrency = $_POST['quote_currency'] ?? '';
            $targetPrice = $_POST['target_price'] ?? '';
            $condition = $_POST['condition'] ?? '';

            if (empty($baseCurrency) || empty($quoteCurrency) || empty($targetPrice) || empty($condition)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: /alerts/create');
                exit;
            }

            if ($this->priceAlert->createAlert($baseCurrency, $quoteCurrency, $targetPrice, $condition)) {
                $_SESSION['success'] = 'Price alert created successfully';
                header('Location: /alerts');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create price alert';
            }
        }
        require_once 'app/views/alerts/create.php';
    }

    public function edit($alertId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $targetPrice = $_POST['target_price'] ?? '';
            $condition = $_POST['condition'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($targetPrice) || empty($condition)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: /alerts/edit/' . $alertId);
                exit;
            }

            if ($this->priceAlert->updateAlert($alertId, $targetPrice, $condition, $isActive)) {
                $_SESSION['success'] = 'Price alert updated successfully';
                header('Location: /alerts');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to update price alert';
            }
        }
        require_once 'app/views/alerts/edit.php';
    }

    public function delete($alertId) {
        if ($this->priceAlert->deleteAlert($alertId)) {
            $_SESSION['success'] = 'Price alert deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete price alert';
        }
        header('Location: /alerts');
        exit;
    }

    public function history($alertId) {
        $alertHistory = $this->priceAlert->getAlertHistory($alertId);
        require_once 'app/views/alerts/history.php';
    }
} 