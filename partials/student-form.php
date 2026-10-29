<?php

$student = $student ?? [];
$errors = $errors ?? [];
$courseOptions = $courseOptions ?? [];
$csrfToken = csrfToken();
?>
<?php if ($errors !== []): ?>
    <div class="alert alert-error" role="alert">
        <p>Please correct the highlighted fields:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="student-form card" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

    <div class="form-grid">
        <div class="form-field">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" minlength="2" maxlength="60" autocomplete="given-name" required value="<?= e($student['first_name'] ?? '') ?>" aria-invalid="<?= isset($errors['first_name']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['first_name'])): ?><small class="field-error"><?= e($errors['first_name']) ?></small><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" minlength="2" maxlength="60" autocomplete="family-name" required value="<?= e($student['last_name'] ?? '') ?>" aria-invalid="<?= isset($errors['last_name']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['last_name'])): ?><small class="field-error"><?= e($errors['last_name']) ?></small><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="255" autocomplete="email" required value="<?= e($student['email'] ?? '') ?>" aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="phone">Phone <span class="optional">(optional)</span></label>
            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="11" autocomplete="tel" value="<?= e($student['phone'] ?? '') ?>" aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['phone'])): ?><small class="field-error"><?= e($errors['phone']) ?></small><?php endif; ?>
            <small>Use an 11-digit number beginning with 07, 08, or 09.</small>
        </div>
        <div class="form-field">
            <label for="course">Course</label>
            <select id="course" name="course" required aria-invalid="<?= isset($errors['course']) ? 'true' : 'false' ?>">
                <option value="">Select a course</option>
                <?php foreach ($courseOptions as $course): ?>
                    <option value="<?= e($course) ?>" <?= ($student['course'] ?? '') === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course'])): ?><small class="field-error"><?= e($errors['course']) ?></small><?php endif; ?>
        </div>
        <div class="form-field">
            <label for="enrolled_on">Enrolled on</label>
            <input id="enrolled_on" name="enrolled_on" type="date" max="<?= e((new DateTimeImmutable('today'))->format('Y-m-d')) ?>" required value="<?= e($student['enrolled_on'] ?? '') ?>" aria-invalid="<?= isset($errors['enrolled_on']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['enrolled_on'])): ?><small class="field-error"><?= e($errors['enrolled_on']) ?></small><?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="index.php">Cancel</a>
        <button class="button" type="submit"><?= e($submitLabel ?? 'Save student') ?></button>
    </div>
</form>
