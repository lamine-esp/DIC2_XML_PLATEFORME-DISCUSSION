<?php
if (empty($members)): ?>
    <div class="text-center py-4">
        <i class="fas fa-users fa-2x text-muted mb-3"></i>
        <p class="text-muted">Aucun membre dans ce groupe</p>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($members as $member): ?>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center p-3 border rounded">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <?php 
                        $firstName = $member['user_info']['profile']['first_name'];
                        $lastName = $member['user_info']['profile']['last_name'];
                        echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                        ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>
                            <?php if ($member['role'] === 'admin'): ?>
                                <span class="badge bg-danger ms-2">Admin</span>
                            <?php elseif ($member['role'] === 'moderator'): ?>
                                <span class="badge bg-warning ms-2">Modérateur</span>
                            <?php endif; ?>
                        </h6>
                        <small class="text-muted">
                            @<?php echo htmlspecialchars($member['user_info']['username']); ?>
                        </small>
                        <br>
                        <small class="text-muted">
                            Membre depuis <?php echo date('d/m/Y', strtotime($member['joined_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?> 