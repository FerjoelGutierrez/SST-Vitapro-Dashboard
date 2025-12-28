<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST | Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Roboto', sans-serif; }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: white; padding: 25px 20px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .card-form { background: white; border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 20px; }
        .form-label { font-weight: 600; color: #374151; font-size: 0.9rem; margin-top: 10px; }
        .section-header { color: #1e3a8a; font-weight: 700; font-size: 1.1rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: center; }
        .section-number { background: #1e3a8a; color: white; width: 25px; height: 25px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; margin-right: 10px; }
        .btn-submit { background: #2563eb; color: white; font-weight: bold; padding: 15px; border-radius: 10px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); transition: 0.3s; }
        .btn-submit:hover { background: #1d4ed8; transform: translateY(-2px); }
        input, select, textarea { background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; }
        input:focus, select:focus, textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); background-color: white; }
    </style>
</head>
<body>

    <div class="header text-center">
        <h3 class="fw-bold mb-1">REPORTE DE SEGURIDAD</h3>
        <p class="small opacity-75 m-0">Gestión de Actos y Condiciones Subestándar</p>
    </div>

    <div class="container" style="max-width: 800px;">
        <form action="api/guardar.php" method="POST" enctype="multipart/form-data">
            
            <div class="card-form">
                <div class="section-header"><span class="section-number">1</span> Identificación y Contexto</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Reportado por:</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Su nombre completo">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Personal:</label>
                        <select name="tipo_usuario" class="form-select" onchange="toggleEmpresa(this.value)">
                            <option value="Interno">Personal Interno (Vitapro)</option>
                            <option value="Contratista">Contratista / Externo</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="divEmpresa" style="display:none;">
                        <label class="form-label">Nombre Empresa:</label>
                        <input type="text" name="empresa" class="form-control" placeholder="Ej: Seguridad X">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Área / Ubicación:</label>
                        <select name="area" class="form-select" required>
                            <option value="">Seleccione área...</option>
                            <option value="Producción">Producción / Planta</option>
                            <option value="Mantenimiento">Mantenimiento / Talleres</option>
                            <option value="Logística">Logística / Bodegas</option>
                            <option value="Calidad">Laboratorios / Calidad</option>
                            <option value="Administración">Oficinas / Administración</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha del Evento:</label>
                        <input type="datetime-local" name="fecha_manual" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-header"><span class="section-number">2</span> Análisis del Hallazgo</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Hallazgo:</label>
                        <select name="hallazgo" id="tipoHallazgo" class="form-select" required onchange="cargarCausas()">
                            <option value="">Seleccione...</option>
                            <option value="Acto Subestándar">⚠️ Acto (Comportamiento Persona)</option>
                            <option value="Condición Subestándar">🔧 Condición (Entorno/Equipo)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Clasificación Específica:</label>
                        <select name="causa_especifica" id="causaEspecifica" class="form-select" required>
                            <option value="General">General</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción Detallada:</label>
                        <textarea name="descripcion" class="form-control" rows="3" required placeholder="Describa claramente qué está sucediendo, qué equipos están involucrados, etc."></textarea>
                    </div>
                </div>
            </div>

            <div class="card-form" style="border-left: 5px solid #2563eb;">
                <div class="section-header"><span class="section-number">3</span> Gestión Inmediata</div>
                
                <div class="mb-3">
                    <label class="form-label text-primary">Acción Correctiva Inmediata (¿Qué hizo usted?):</label>
                    <textarea name="accion_inmediata" class="form-control" rows="2" placeholder="Ej: Detuve la máquina, coloqué cinta de peligro, hablé con el trabajador..."></textarea>
                    <div class="form-text">Importante: Describa la acción tomada para controlar el riesgo al momento.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nivel de Riesgo:</label>
                        <select name="riesgo" class="form-select fw-bold">
                            <option value="Bajo" class="text-success">🟢 Bajo</option>
                            <option value="Medio" class="text-warning">🟡 Medio</option>
                            <option value="Alto" class="text-danger">🔴 Alto (Crítico)</option>
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
                        <label class="form-label">Aviso SAP (Opcional):</label>
                        <input type="text" name="sap" class="form-control" placeholder="N° Aviso">
                    </div>
                </div>
            </div>

            <div class="card-form">
                <div class="section-header"><span class="section-number">4</span> Evidencia</div>
                <label class="form-label">Fotografía del Evento:</label>
                <input type="file" name="foto" class="form-control" accept="image/*" capture="environment">
            </div>

            <div class="d-grid mb-5">
                <button type="submit" class="btn btn-submit btn-lg">ENVIAR REPORTE OFICIAL</button>
            </div>

        </form>
    </div>

    <script>
        function toggleEmpresa(val) {
            document.getElementById('divEmpresa').style.display = (val === 'Contratista') ? 'block' : 'none';
        }
        function cargarCausas() {
            const tipo = document.getElementById('tipoHallazgo').value;
            const select = document.getElementById('causaEspecifica');
            select.innerHTML = "";
            const actos = ["No uso de EPP", "Operar sin autorización", "Uso incorrecto de herramientas", "Posición inadecuada", "Juegos / Distracción", "Exceso de velocidad", "Incumplimiento de procedimiento"];
            const condiciones = ["Desorden / Falta limpieza", "Herramientas defectuosas", "Ruido / Iluminación", "Falta señalización", "Piso resbaloso/irregular", "Riesgo eléctrico", "Protecciones faltantes"];
            let lista = (tipo.includes("Acto")) ? actos : condiciones;
            lista.forEach(op => {
                let option = document.createElement("option"); option.text = op; option.value = op; select.add(option);
            });
        }
    </script>
</body>
</html>
