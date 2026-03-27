<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="main">
        <div class="inner">
            <h2 class="heading-main">Task Board</h2>

            <div class="task-list">
                <?php if (!empty($tasks)) : ?>
                    <?php foreach ($tasks as $task) : ?>
                        <div class="task-item">
                            <h3>
                                <?= htmlspecialchars($task['key']) ?>
                            </h3>
                            <p>
                                <?= htmlspecialchars($task['fields']['summary']) ?>
                            </p>
                            <small>
                                Status: <?= htmlspecialchars($task['fields']['status']['name']) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No tasks found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>