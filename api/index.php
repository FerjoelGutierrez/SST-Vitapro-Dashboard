<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST | Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; padding-bottom: 40px; }
        
        /* HEADER DIFERENTE PARA NOTAR EL CAMBIO */
        .brand-header { 
            background: #0f172a; /* Azul casi negro */
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
            margin-bottom: -40px; 
            padding-bottom: 60px;
        }
        
        .main-card { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            padding: 40px; 
            border: 1px solid #e2e8f0;
        }

        .form-label { font-weight: 700; font-size: 0.85rem; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-title { 
            color: #2563eb; 
            font-weight: 800; 
            font-size: 1.1rem; 
            border-bottom: 2px solid #e2e8f0; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
            margin-top: 30px;
        }
        
        /* CAMPO DE ACCIÓN DESTACADO */
        .action-field { 
            background: #eff6ff; 
            border: 1px solid #bfdbfe; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
        }
        
        .btn-send { background: #0f172a; border: none; padding: 15px; font-weight: 700; width: 100%; font-size: 1.1rem; transition: 0.3s; }
        .btn-send:hover { background: #2563eb; transform: translateY(-2px); }
    </style>
</head>
<body>

    <div class="brand-header">
        <h2 class="fw-bold m-0">REPORTE DE SEGURIDAD</h2>
        <small style="opacity: 0.7; letter-spacing: 2px;">SISTEMA DE GESTIÓN SST</small>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="api/guardar.php" method="POST" enctype="multipart/form-data" class="main-card">
                    
                    <div class="alert alert-info text-center small mb-4">
                        <i class="fas fa-info-circle"></i> Complete todos los campos marcados con *
                    </div>

                    <div class="section-title" style="margin-top:0;">1. DATOS GENERALES</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Reportado Por *</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Nombre y Apellido">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Personal *</label>
                            <select name="tipo_usuario" class="form-select" onchange="toggleEmpresa(this.value)">
                                <option value="Interno">Personal Interno</option>
                                <option value="Contratista">Contratista</option>
                            </select>
                        </div>
                        <div class="col-12" id="divEmpresa" style="display:none;">
                            <label class="form-label">Empresa Contratista</label>
                            <input type="text" name="empresa" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Área del Evento *</label>
                            <select name="area" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Producción">Producción</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Logística">Logística / Bodega</option>
                                <option value="Calidad">Calidad</option>
                                <option value="Administración">Oficinas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha del Evento</label>
                            <input type="datetime-local" name="fecha_manual" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>

                    <div class="section-title">2. DETALLE DEL HALLAZGO</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo *</label>
                            <select name="hallazgo" id="tipoHallazgo" class="form-select" required onchange="cargarCausas()">
                                <option value="">Seleccione...</option>
                                <option value="Acto Subestándar">⚠️ Acto (Comportamiento)</option>
                                <option value="Condición Subestándar">🔧 Condición (Entorno)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Clasificación *</label>
                            <select name="causa_especifica" id="causaEspecifica" class="form-select" required>
                                <option value="General">General</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción Detallada *</label>
                            <textarea name="descripcion" class="form-control" rows="3" required placeholder="¿Qué sucedió? Describa el evento..."></textarea>
                        </div>
                    </div>

                    <div class="section-title">3. GESTIÓN DEL RIESGO</div>
                    
                    <div class="action-field">
                        <label class="form-label text-primary">✅ Acción Inmediata Tomada *</label>
                        <textarea name="accion_inmediata" class="form-control border-primary" rows="2" required placeholder="Ej: Se detuvo el trabajo, se colocó señalización, se reportó al supervisor..."></textarea>
                        <div class="form-text text-muted">Describa qué hizo usted en ese momento para corregir o controlar el riesgo.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nivel de Riesgo *</label>
                            <select name="riesgo" class="form-select fw-bold">
                                <option value="Bajo" class="text-success">🟢 Bajo</option>
                                <option value="Medio" class="text-warning">🟡 Medio</option>
                                <option value="Alto" class="text-danger">🔴 Alto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">¿Detuvo Actividad?</label>
                            <select name="detuvo" class="form-select">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Aviso SAP (Opcional)</label>
                            <input type="text" name="sap" class="form-control" placeholder="1000...">
                        </div>
                    </div>

                    <div class="section-title">4. EVIDENCIA</div>
                    <div class="mb-4">
                        <label class="form-label">Fotografía</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" capture="environment">
                    </div>

                    <button type="submit" class="btn btn-primary btn-send">ENVIAR REPORTE</button>

                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleEmpresa(val) {
            document.getElementById('divEmpresa').style.display = (val === 'Contratista') ? 'block' : 'none';
        }
        function cargarCausas() {
            const tipo = document.getElementById('tipoHallazgo').value;
            const select = document.getElementById('causaEspecifica');
            select.innerHTML = "";
            const actos = ["No uso de EPP", "Operar sin autorización", "Uso incorrecto de herramientas", "Posición inadecuada", "Juegos / Distracción", "Exceso de velocidad"];
            const condiciones = ["Falta de orden/limpieza", "Herramientas defectuosas", "Ruido / Iluminación", "Falta señalización", "Piso resbaloso", "Riesgo eléctrico"];
            let lista = (tipo.includes("Acto")) ? actos : condiciones;
            lista.forEach(op => {
                let option = document.createElement("option"); option.text = op; option.value = op; select.add(option);
            });
        }
    </script>
</body>
</html>
