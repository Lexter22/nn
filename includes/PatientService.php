<?php
require_once '../config/database.php';

class PatientService {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function checkOrCreatePatient($name, $type) {
        // Check if patient exists
        $query = "SELECT id FROM patients WHERE name = :name AND type = :type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }

        // Generate unique client ID
        $client_id = $this->generateClientId();
        
        // Insert new patient
        $query = "INSERT INTO patients (client_id, name, type) VALUES (:client_id, :name, :type)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    private function generateClientId() {
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM patients WHERE client_id LIKE 'CL-$year-%'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_number = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return "CL-$year-$next_number";
    }

    public function addMedicalRecord($patient_id, $data) {
        $query = "INSERT INTO clinic_records (patient_id, diagnosis, treatment, date_added) 
                  VALUES (:patient_id, :diagnosis, :treatment, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':diagnosis', $data['diagnosis']);
        $stmt->bindParam(':treatment', $data['treatment']);
        return $stmt->execute();
    }

    public function searchPatients($query) {
        $search = "%$query%";
        $sql = "SELECT * FROM patients WHERE name LIKE :search OR client_id LIKE :search ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':search', $search);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRecords() {
        $query = "SELECT cr.*, p.name, p.client_id 
                  FROM clinic_records cr 
                  JOIN patients p ON cr.patient_id = p.id 
                  ORDER BY cr.date_added DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteRecord($record_id) {
        $query = "DELETE FROM clinic_records WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $record_id);
        return $stmt->execute();
    }
}
?>