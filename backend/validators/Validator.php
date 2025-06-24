<?php
// Clase para revisar que los datos del contacto sean correctos
class Validator {
    public static function validateContact($data) {
        $errors = []; 

        // revisa que el nombre no esté vacío
        if (empty($data->first_name)) {
            $errors[] = "El nombre es obligatorio";
        }

        // Revisa que el apellido no esté vacío
        if (empty($data->last_name)) {
            $errors[] = "El apellido es obligatorio";
        }

        // Revisa que el email sea válido y no esté vacío
        if (empty($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Necesitas un correo electrónico válido";
        }

        // Revisa los números de teléfono, si los hay
        if (isset($data->phones) && is_array($data->phones)) {
            foreach ($data->phones as $phone) {
                if (!preg_match("/^\+?\d{1,15}$/", $phone)) {
                    $errors[] = "El número de teléfono $phone no es válido";
                }
            }
        }

        return $errors; 
    }
}
?>