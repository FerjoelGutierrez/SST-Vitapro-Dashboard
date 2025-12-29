<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST | Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        body { 
            background-color: #f1f5f9; 
            font-family: 'Inter', sans-serif; 
            color: #334155;
        }
        /* Botón Flotante Admin */
        .admin-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #0f172a;
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .admin-btn:hover { background: #334155; transform: translateY(-2px); color: white;}

        /* Header Corporativo */
        .header { 
            background: linear-gradient(135deg, #002b49 0%, #0f172a 100%); 
            color: white; 
            padding: 40px 0; 
            margin-bottom: -40px; 
            text-align: center; 
            padding-bottom: 80px; /* Espacio para que la tarjeta suba */
        }
        
        /* Tarjeta del Formulario */
        .card-form { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); 
            border: 1px solid #e2e8f0; 
            position: relative;
        }

        .section-title { 
            color: #002b49; 
            font-weight: 800; 
            border-bottom: 2px solid #e2e8f0; 
            padding-bottom: 10px; 
            margin: 30px 0 20px 0; 
            text-transform: uppercase; 
            font-size: 0.85rem; 
            letter-spacing: 0.05em;
        }
        
        .form-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.3rem; }
        .form-control, .form-select {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Botón Enviar */
        .btn-submit { 
            background: #2563eb; 
            color: white; 
            width: 100%; 
            padding: 16px; 
            font-weight: 800; 
            border: none; 
            border-radius: 8px; 
            margin-top: 30px; 
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

        .action-box { 
            background: #eff6ff; 
            border: 1px solid #bfdbfe; 
            padding: 20px; 
            border-radius: 8px; 
            border-left: 5px solid #2563eb; 
        }
    </style>
</head>
<body>

    <a href="login.php" class="admin-btn">🔒 Admin Panel</a>

    <div class="header">
        <h2 class="fw-bold m-0">REPORTE DE SEGURIDAD</h2>
        <small style="opacity:0.8; letter-spacing:1px; font-weight: 300;">SISTEMA DE GESTIÓN SST - VITAPRO</small>
    </div>

    <div class="container pb-5">
        <form action="guardar.php" method="POST" enctype="multipart/form-data" class="card-form" id="sstForm">
            
            <div class="section-title" style="margin-top:0;">1. Información General</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Reportado Por <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Nombre completo">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Área <span class="text-danger">*</span></label>
                    <select name="area" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="Producción">Producción</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Logística">Logística</option>
                        <option value="Calidad">Calidad</option>
                        <option value="Administración">Administración</option>
                        <option value="Seguridad">Seguridad</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo Usuario</label>
                    <select name="tipo_usuario" class="form-select" id="tipoUsuario" onchange="toggleEmpresa()">
                        <option value="Interno">Interno</option>
                        <option value="Contratista">Contratista</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Empresa Contratista</label>
                    <input type="text" name="empresa_contratista" id="empresaInput" class="form-control" placeholder="Solo si es contratista" disabled>
                </div>
            </div>

            <div class="section-title">2. Detalle del Hallazgo</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Hallazgo <span class="text-danger">*</span></label>
                    <select name="tipo_hallazgo" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="Acto Subestándar">Acto Subestándar</option>
                        <option value="Condición Subestándar">Condición Subestándar</option>
                        <option value="Incidente Ambiental">Incidente Ambiental</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Causa / Clasificación</label>
                    <select name="causa_especifica" class="form-select">
                        <option value="General">General</option>
                        <option value="Falta de EPP">Falta de EPP</option>
                        <option value="Herramientas Defectuosas">Herramientas Defectuosas</option>
                        <option value="Procedimiento Incorrecto">Incumplimiento Proc.</option>
                        <option value="Orden y Limpieza">Orden y Limpieza</option>
                        <option value="Condiciones Eléctricas">Condiciones Eléctricas</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción del Evento <span class="text-danger">*</span></label>
                    <textarea name="descripcion" class="form-control" rows="3" required placeholder="Describa detalladamente qué sucedió o qué observó..."></textarea>
                </div>
            </div>

            <div class="section-title">3. Gestión Inmediata</div>
            
            <div class="action-box mb-3">
                <label class="form-label fw-bold text-primary">✅ Acción Correctiva Inmediata</label>
                <textarea name="accion_correctiva" class="form-control" rows="2" placeholder="¿Qué hizo usted para controlar el riesgo? Ej: Se detuvo equipo, se colocó cinta..."></textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nivel de Riesgo</label>
                    <select name="nivel_riesgo" class="form-select">
                        <option value="Bajo">Bajo (Verde)</option>
                        <option value="Medio">Medio (Amarillo)</option>
                        <option value="Alto">Alto (Rojo)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">¿Detuvo actividad?</label>
                    <select name="detuvo_actividad" class="form-select">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Aviso SAP (Opcional)</label>
                    <input type="text" name="aviso_sap" class="form-control" placeholder="Ej: 10203040">
                </div>
            </div>

            <div class="section-title">4. Evidencia Fotográfica</div>
            <div class="mb-3">
                <input type="file" name="foto" class="form-control" accept="image/*">
                <div class="form-text">Formatos permitidos: JPG, PNG. Máx 5MB.</div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">ENVIAR REPORTE</button>
        </form>
    </div>

    <script>
        // 1. Lógica para habilitar campo empresa si es contratista
        function toggleEmpresa() {
            const tipo = document.getElementById('tipoUsuario').value;
            const input = document.getElementById('empresaInput');
            if(tipo === 'Contratista') {
                input.disabled = false;
                input.required = true;
                input.placeholder = "Nombre de la empresa";
            } else {
                input.disabled = true;
                input.required = false;
                input.value = "";
                input.placeholder = "Solo si es contratista";
            }
        }

        // 2. Detectar si el reporte se guardó correctamente (mensaje URL)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            Swal.fire({
                title: '¡Reporte Guardado!',
                text: 'La información ha sido registrada en el sistema.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Entendido'
            }).then(() => {
                // Limpiar la URL
                window.history.replaceState(null, null, window.location.pathname);
            });
        }

        // 3. Efecto de carga al enviar (Evita doble clic)
        document.getElementById('sstForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '⏳ ENVIANDO...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
