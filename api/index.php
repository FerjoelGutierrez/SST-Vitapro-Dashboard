<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SST Vitapro - Reporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --blue: #1e3a8a; --light: #3b82f6; }
        body { font-family: 'Outfit', sans-serif; background: #f1f5f9; padding: 20px 0; }
        .main-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; max-width: 800px; margin: 0 auto; }
        .header-banner { background: linear-gradient(135deg, var(--blue), var(--light)); color: white; padding: 35px; text-align: center; }
        .form-section { padding: 40px; }
        .form-label { font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 0.9rem; }
        .btn-submit { background: var(--blue); color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 700; width: 100%; transition: 0.3s; margin-top: 20px; }
        .btn-submit:hover { transform: translateY(-2px); background: #1e40af; box-shadow: 0 5px 15px rgba(30,58,138,0.3); }
        .photo-box { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; background: #f8fafc; transition: 0.3s; }
        .photo-box:hover { border-color: var(--light); background: #eff6ff; }
        .footer-link { text-align: center; padding: 20px; }
        .admin-trigger { color: #94a3b8; text-decoration: none; font-size: 13px; opacity: 0.6; }
        .required-field::after { content: " *"; color: red; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Notificación de éxito -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 mb-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i><strong>¡Enviado!</strong> El reporte se guardó correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="main-card">
            <div class="header-banner">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                <h2 class="fw-bold mb-0">Sistema de Gestión SST</h2>
                <p class="mb-0 opacity-75">Vitapro - Cero Accidentes</p>
            </div>
            <div class="form-section">
                <form action="guardar.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required-field">Usted es:</label>
                            <select name="tipo_usuario" id="tipo_usuario" class="form-select" required onchange="actualizarEmpresa()">
                                <option value="Interno">Personal Interno</option>
                                <option value="Contratista">Contratista</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Nombre Completo:</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="col-12" id="divEmpresa" style="display:none;">
                            <label class="form-label required-field">Empresa Contratista:</label>
                            <input type="text" name="empresa_contratista" id="empresa_input" class="form-control" placeholder="Nombre comercial de la empresa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Área del Evento:</label>
                            <select name="area" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option>Producción</option><option>Mantenimiento</option><option>Logística</option><option>Almacén</option><option>Administración</option><option>Calidad</option><option>Seguridad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Hallazgo:</label>
                            <select name="tipo_hallazgo" id="tipo_hallazgo" class="form-select" required onchange="actualizarCausas()">
                                <option value="">Seleccione...</option>
                                <option value="Acto Subestándar">Acto Subestándar</option>
                                <option value="Condición Subestándar">Condición Subestándar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Nivel de Riesgo:</label>
                            <select name="nivel_riesgo" class="form-select" required>
                                <option value="Bajo">🟢 Bajo</option>
                                <option value="Medio">🟡 Medio</option>
                                <option value="Alto">🔴 Alto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required-field">Clasificación Específica:</label>
                            <select name="causa_especifica" id="causa_especifica" class="form-select" required disabled>
                                <option value="">Primero seleccione hallazgo...</option>
                            </select>
                        </div>
                        
                        <!-- NUEVOS CAMPOS DE SEGUIMIENTO -->
                        <div class="col-md-6">
                            <label class="form-label">N° Aviso SAP (Opcional):</label>
                            <input type="text" name="aviso_sap" class="form-control" placeholder="Ej: 10002456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">¿Se detuvo actividad?</label>
                            <select name="detuvo_actividad" class="form-select">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label required-field">Descripción Detallada:</label>
                            <textarea name="descripcion" class="form-control" rows="3" required placeholder="Explique qué sucedió y qué medidas inmediatas se tomaron..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label required-field">Evidencia Fotográfica:</label>
                            <div class="photo-box" onclick="document.getElementById('foto').click()">
                                <i class="fas fa-camera fa-2x mb-2 text-muted"></i>
                                <p class="mb-0 fw-bold" id="fileName">Toque para seleccionar foto</p>
                            </div>
                            <input type="file" name="foto" id="foto" class="d-none" accept="image/*" required onchange="actualizarNombreFoto(this)">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-submit">ENVIAR REPORTE OFICIAL</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="footer-link">
            <a href="#" class="admin-trigger" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-lock"></i> Acceso Administrativo</a>
        </div>
    </div>
    <!-- Modal Login -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <h5 class="fw-bold mb-3">Login Admin</h5>
                    <form action="login.php" method="POST">
                        <input type="text" name="usuario" class="form-control mb-2" placeholder="Usuario" required>
                        <input type="password" name="password" class="form-control mb-3" placeholder="Clave" required>
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function actualizarEmpresa() {
            const tipo = document.getElementById('tipo_usuario').value;
            const div = document.getElementById('divEmpresa');
            const input = document.getElementById('empresa_input');
            if (tipo === 'Contratista') {
                div.style.display = 'block';
                input.required = true;
            } else {
                div.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }
        function actualizarCausas() {
            const tipo = document.getElementById('tipo_hallazgo').value;
            const causa = document.getElementById('causa_especifica');
            causa.innerHTML = '<option value="">Seleccione...</option>';
            
            if (!tipo) {
                causa.disabled = true;
                return;
            }
            
            causa.disabled = false;
            const opciones = {
                'Acto Subestándar': [
                    'No uso de EPP', 
                    'Uso incorrecto de EPP', 
                    'Distracción', 
                    'Procedimiento incorrecto', 
                    'Postura incorrecta', 
                    'Uso de celular',
                    'Otros'
                ],
                'Condición Subestándar': [
                    'Herramientas defectuosas', 
                    'Piso resbaladizo', 
                    'Cables expuestos', 
                    'Iluminación deficiente', 
                    'Falta de orden y limpieza',
                    'Equipos sin protección',
                    'Otros'
                ]
            };
            
            if (opciones[tipo]) {
                opciones[tipo].forEach(opt => {
                    let el = document.createElement('option');
                    el.value = el.textContent = opt;
                    causa.appendChild(el);
                });
            }
        }
        function actualizarNombreFoto(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileName').innerHTML = '<i class="fas fa-check-circle text-success fs-5"></i><br>' + input.files[0].name;
            }
        }
    </script>
</body>
</html>
