<?php
// Sample database search functions using PDO prepared statements
// This demonstrates the proper SQL implementation for the search features

class DatabaseSearch {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Search medicines by name or category
     * SQL: WHERE name LIKE :search OR category LIKE :search
     */
    public function searchMedicines($search) {
        $sql = "SELECT * FROM medicines 
                WHERE name LIKE :search OR category LIKE :search 
                ORDER BY name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search patient records by patient name or diagnosis
     * SQL: WHERE patient_name LIKE :search OR diagnosis LIKE :search
     */
    public function searchPatientRecords($search) {
        $sql = "SELECT * FROM patient_records 
                WHERE patient_name LIKE :search OR diagnosis LIKE :search 
                ORDER BY date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all medicines (no search)
     */
    public function getAllMedicines() {
        $sql = "SELECT * FROM medicines ORDER BY name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all patient records (no search)
     */
    public function getAllPatientRecords() {
        $sql = "SELECT * FROM patients ORDER BY date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Usage example:
/*
require_once '../config/database.php';

$dbSearch = new DatabaseSearch($pdo);

// Search medicines
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $medicines = $dbSearch->searchMedicines($_GET['search']);
} else {
    $medicines = $dbSearch->getAllMedicines();
}

// Search patient records
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $records = $dbSearch->searchPatientRecords($_GET['search']);
} else {
    $records = $dbSearch->getAllPatientRecords();
}
*/
?>