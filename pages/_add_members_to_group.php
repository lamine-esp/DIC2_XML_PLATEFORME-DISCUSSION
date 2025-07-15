<form method="post" id="addMembersForm">
    <input type="hidden" name="action" value="add_members_to_group">
    <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($_GET['group_id']); ?>">
    
    <div class="mb-3">
        <h6><i class="fas fa-users"></i> Ajouter des membres au groupe</h6>
        <p class="text-muted small">Sélectionnez les utilisateurs que vous souhaitez ajouter au groupe</p>
    </div>
    
    <!-- Onglets pour les types de contacts -->
    <ul class="nav nav-tabs mb-3" id="contactTypeTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-list" type="button" role="tab">
                <i class="fas fa-user"></i> Tous les utilisateurs (<?php echo count($allUsers); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-list" type="button" role="tab">
                <i class="fas fa-address-book"></i> Mes contacts (<?php echo count($userContacts); ?>)
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="contactTypeTabsContent">
        <!-- Onglet Utilisateurs -->
        <div class="tab-pane fade show active" id="users-list" role="tabpanel">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="userSearch" placeholder="Rechercher un utilisateur par nom ou nom d'utilisateur...">
                </div>
            </div>
            
            <!-- Sélection multiple -->
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllUsers()">
                    <i class="fas fa-check-square"></i> Tout sélectionner
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllUsers()">
                    <i class="fas fa-square"></i> Tout désélectionner
                </button>
            </div>
            
            <div class="row" id="usersContainer">
                <?php 
                $availableUsers = [];
                foreach ($allUsers as $userData): 
                    if ($userData['id'] !== $currentUser['id'] && !$group->isMember($_GET['group_id'], $userData['id'])):
                        $availableUsers[] = $userData;
                ?>
                    <div class="col-md-6 mb-2 user-item">
                        <div class="form-check border rounded p-2 user-card">
                            <input class="form-check-input user-checkbox" type="checkbox" name="selected_contacts[]" 
                                   value="<?php echo $userData['id']; ?>" 
                                   id="user_<?php echo $userData['id']; ?>">
                            <label class="form-check-label w-100" for="user_<?php echo $userData['id']; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.9em;">
                                        <?php 
                                        $firstName = $userData['profile']['first_name'];
                                        $lastName = $userData['profile']['last_name'];
                                        echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                        ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></strong>
                                        <br>
                                        <small class="text-muted">@<?php echo htmlspecialchars($userData['username']); ?></small>
                                        <?php if (isset($userData['profile']['bio']) && !empty($userData['profile']['bio'])): ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($userData['profile']['bio'], 0, 30)); ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-2">
                                        <span class="badge bg-<?php echo $userData['status'] === 'online' ? 'success' : ($userData['status'] === 'away' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($userData['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                
                if (empty($availableUsers)):
                ?>
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Tous les utilisateurs sont déjà membres de ce groupe</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Onglet Contacts -->
        <div class="tab-pane fade" id="contacts-list" role="tabpanel">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="contactSearch" placeholder="Rechercher un contact...">
                </div>
            </div>
            
            <!-- Sélection multiple -->
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllContacts()">
                    <i class="fas fa-check-square"></i> Tout sélectionner
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllContacts()">
                    <i class="fas fa-square"></i> Tout désélectionner
                </button>
            </div>
            
            <div class="row" id="contactsContainer">
                <?php 
                $availableContacts = [];
                foreach ($userContacts as $contactData): 
                    // Ne permettre que les contacts qui sont des utilisateurs de la plateforme
                    if ($contactData['type'] === 'user' && 
                        !empty($contactData['contact_user_id']) && 
                        !$group->isMember($_GET['group_id'], $contactData['contact_user_id'])):
                        $availableContacts[] = $contactData;
                ?>
                    <div class="col-md-6 mb-2 contact-item">
                        <div class="form-check border rounded p-2 contact-card">
                            <input class="form-check-input contact-checkbox" type="checkbox" name="selected_contacts[]" 
                                   value="<?php echo $contactData['contact_user_id']; ?>" 
                                   id="contact_<?php echo $contactData['id']; ?>">
                            <label class="form-check-label w-100" for="contact_<?php echo $contactData['id']; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.9em;">
                                        <?php 
                                        $contactUser = $user->getUserById($contactData['contact_user_id']);
                                        if ($contactUser && isset($contactUser['profile'])) {
                                            $firstName = $contactUser['profile']['first_name'];
                                            $lastName = $contactUser['profile']['last_name'];
                                            echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                        } else {
                                            echo '?';
                                        }
                                        ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>
                                            <?php 
                                            $contactUser = $user->getUserById($contactData['contact_user_id']);
                                            if ($contactUser && isset($contactUser['profile'])) {
                                                $displayName = $contactData['nickname'] ?: $contactUser['profile']['first_name'] . ' ' . $contactUser['profile']['last_name'];
                                                echo htmlspecialchars($displayName);
                                            } else {
                                                echo htmlspecialchars('Utilisateur inconnu');
                                            }
                                            ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php 
                                            $contactUser = $user->getUserById($contactData['contact_user_id']);
                                            if ($contactUser) {
                                                echo '@' . htmlspecialchars($contactUser['username']);
                                            } else {
                                                echo 'Utilisateur supprimé';
                                            }
                                            ?>
                                        </small>
                                        <?php if ($contactData['is_favorite']): ?>
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-star"></i> Favori
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                
                if (empty($availableContacts)):
                ?>
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-address-book fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucun contact utilisateur disponible à ajouter à ce groupe</p>
                        <small class="text-muted">Note : Seuls les contacts qui sont des utilisateurs de la plateforme peuvent être ajoutés aux groupes</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <div class="me-auto">
            <span class="text-muted" id="selectedCount">0 utilisateur(s) sélectionné(s)</span>
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
            <i class="fas fa-user-plus"></i> Ajouter les membres
        </button>
    </div>
</form>

<script>
// Recherche dans les utilisateurs
document.getElementById('userSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const userItems = document.querySelectorAll('#usersContainer .user-item');
    
    userItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Recherche dans les contacts
document.getElementById('contactSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const contactItems = document.querySelectorAll('#contactsContainer .contact-item');
    
    contactItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Sélection multiple pour les utilisateurs
function selectAllUsers() {
    const checkboxes = document.querySelectorAll('#usersContainer .user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

function deselectAllUsers() {
    const checkboxes = document.querySelectorAll('#usersContainer .user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

// Sélection multiple pour les contacts
function selectAllContacts() {
    const checkboxes = document.querySelectorAll('#contactsContainer .contact-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

function deselectAllContacts() {
    const checkboxes = document.querySelectorAll('#contactsContainer .contact-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

// Mise à jour du compteur de sélection
function updateSelectedCount() {
    const allCheckboxes = document.querySelectorAll('input[name="selected_contacts[]"]:checked');
    const count = allCheckboxes.length;
    const countElement = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    
    countElement.textContent = count + ' utilisateur(s) sélectionné(s)';
    submitBtn.disabled = count === 0;
}

// Écouter les changements de checkbox
document.addEventListener('change', function(e) {
    if (e.target.name === 'selected_contacts[]') {
        updateSelectedCount();
    }
});

// Initialiser le compteur
updateSelectedCount();
</script> 