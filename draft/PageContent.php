<?php
// File: /cms/PageContent.php

require_once 'Database.php';

class PageContent {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM page_content ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM page_content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO page_content (page_name, section_name, title, subtitle, image_path, icon_class, description, extra_description, officer_name, officer_position, clinic_process_steps, clinic_downloadable_forms, contact_phone, contact_email, contact_location, facebook_link) 
            VALUES (:page_name, :section_name, :title, :subtitle, :image_path, :icon_class, :description, :extra_description, :officer_name, :officer_position, :clinic_process_steps, :clinic_downloadable_forms, :contact_phone, :contact_email, :contact_location, :facebook_link)");

        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->conn->prepare("UPDATE page_content SET 
            page_name = :page_name, section_name = :section_name, title = :title, subtitle = :subtitle, image_path = :image_path, icon_class = :icon_class, description = :description, extra_description = :extra_description, officer_name = :officer_name, officer_position = :officer_position, clinic_process_steps = :clinic_process_steps, clinic_downloadable_forms = :clinic_downloadable_forms, contact_phone = :contact_phone, contact_email = :contact_email, contact_location = :contact_location, facebook_link = :facebook_link 
            WHERE id = :id");

        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM page_content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
