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
        $sql = "SELECT id, name, category, stock_quantity, expiry_date, status
                FROM medicines
                WHERE name LIKE :search OR category LIKE :search
                ORDER BY name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $term = '%' . $search . '%';
        $stmt->bindParam(':search', $term, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search patient visits by patient name or symptom/notes/treatment
     * SQL: WHERE patient_name LIKE :search OR diagnosis LIKE :search
     */
    public function searchPatientRecords($search) {
        $sql = "SELECT v.visit_date, p.name, v.bp, v.temp, v.symptom, v.notes, v.treatment, v.disposition
                FROM visits v
                JOIN patients p ON p.id = v.patient_id
                WHERE p.name LIKE :q OR v.symptom LIKE :q OR v.notes LIKE :q OR v.treatment LIKE :q
                ORDER BY v.visit_date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $term = '%' . $search . '%';
        $stmt->bindParam(':q', $term, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all medicines (no search)
     */
    public function getAllMedicines() {
        $stmt = $this->pdo->prepare("SELECT id, name, category, stock_quantity, expiry_date, status FROM medicines ORDER BY name ASC");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all patient visit records (no search)
     */
    public function getAllPatientRecords() {
        $stmt = $this->pdo->prepare("SELECT v.visit_date, p.name, v.bp, v.temp, v.symptom, v.notes, v.treatment, v.disposition
                                     FROM visits v
                                     JOIN patients p ON p.id = v.patient_id
                                     ORDER BY v.visit_date DESC");
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