-- Création de la table todolist pour SQLite
-- Compatible avec le modèle todolist.php

CREATE TABLE IF NOT EXISTS todolist (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INTEGER NOT NULL,
    assigned_to INTEGER,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed')),
    priority VARCHAR(10) NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
    due_date DATE,
    completed_at DATETIME,
    completed_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Clés étrangères
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_todolist_status ON todolist(status);
CREATE INDEX IF NOT EXISTS idx_todolist_priority ON todolist(priority);
CREATE INDEX IF NOT EXISTS idx_todolist_created_by ON todolist(created_by);
CREATE INDEX IF NOT EXISTS idx_todolist_assigned_to ON todolist(assigned_to);
CREATE INDEX IF NOT EXISTS idx_todolist_due_date ON todolist(due_date);
CREATE INDEX IF NOT EXISTS idx_todolist_created_at ON todolist(created_at);

-- Trigger pour mettre à jour automatiquement updated_at
CREATE TRIGGER IF NOT EXISTS update_todolist_updated_at 
    AFTER UPDATE ON todolist
    FOR EACH ROW
BEGIN
    UPDATE todolist SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;