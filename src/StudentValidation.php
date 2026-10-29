<?php

function validateStudent(array $input, array $courseOptions, ?int $excludeId = null): array
{
    $student = [
        'first_name' => is_string($input['first_name'] ?? null) ? trim($input['first_name']) : '',
        'last_name' => is_string($input['last_name'] ?? null) ? trim($input['last_name']) : '',
        'email' => is_string($input['email'] ?? null) ? trim($input['email']) : '',
        'phone' => is_string($input['phone'] ?? null) ? trim($input['phone']) : '',
        'course' => is_string($input['course'] ?? null) ? trim($input['course']) : '',
        'enrolled_on' => is_string($input['enrolled_on'] ?? null) ? trim($input['enrolled_on']) : '',
    ];
    $errors = [];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($student[$field] === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (!preg_match('/^[\p{L} -]{2,60}$/u', $student[$field])) {
            $errors[$field] = $label . ' must be 2–60 characters and use only letters, spaces, or hyphens.';
        }
    }

    if ($student['email'] === '' || filter_var($student['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    } elseif (strlen($student['email']) > 255) {
        $errors['email'] = 'Email must be 255 characters or fewer.';
    } else {
        $sql = 'SELECT id FROM students WHERE email = :email';
        $parameters = ['email' => $student['email']];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $parameters['exclude_id'] = $excludeId;
        }
        $statement = db()->prepare($sql);
        $statement->execute($parameters);
        if ($statement->fetch() !== false) {
            $errors['email'] = 'That email address is already in use.';
        }
    }

    if ($student['phone'] !== '' && !preg_match('/^0[789][01]\d{8}$/', $student['phone'])) {
        $errors['phone'] = 'Enter a valid 11-digit phone number, or leave this field empty.';
    }

    if (!in_array($student['course'], $courseOptions, true)) {
        $errors['course'] = 'Select one of the available courses.';
    }

    $enrolledDate = DateTimeImmutable::createFromFormat('!Y-m-d', $student['enrolled_on']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $enrolledDate === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $enrolledDate->format('Y-m-d') !== $student['enrolled_on']
    ) {
        $errors['enrolled_on'] = 'Enter a valid enrollment date.';
    } elseif ($enrolledDate > new DateTimeImmutable('today')) {
        $errors['enrolled_on'] = 'Enrollment date cannot be in the future.';
    }

    return [$student, $errors];
}
