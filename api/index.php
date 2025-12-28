<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST Vitapro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        .header-brand { background: #0f172a; padding: 20px; color: white; text-align: center; border-bottom: 4px solid #2563eb; }
        .form-card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 30px; margin-top: -30px; margin-bottom: 50px; }
        .section-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; margin-top: 20px; }
        .btn-submit { background: #2563eb; border: none; padding: 15px; font-weight: 700; letter-spacing: 0.5px; }
        .btn-submit:hover { background: #1d4ed8; }
        label { font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header-brand">
        <h2 class="fw-bold mb-0">VITAPRO <span class="text-primary">SST</span></h2>
        <p class="small opacity-75">Reporte de Actos y Condiciones Subestándar</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="api/guardar.php" method="POST" enctype="multipart/form-data" class="form-card">
                    
                    <div class="section-title">1. Identificación del Reportante</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Tipo de Usuario</label>
                            <select name="tipo_usuario" class="form-select" onchange="toggleEmpresa(this.value)">
                                <option value="Interno">Personal Interno</option>
                                <option value="Contratista">Contratista / Visita</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="divEmpresa" style="display:none;">
                            <label>Empresa</label>
                            <input type="text" name="empresa" class="form-control" placeholder="Nombre de la empresa">
                        </div>
                        <div class="col-12">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="col-md-6">
                            <label>Área del Evento</label>
                            <select name="area" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Producción">Producción</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Logística">Logística</option>
                                <option value="Calidad">Calidad</option>
                                <option value="Administración">Administración</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Nivel de Riesgo (Percepción)</label>
                            <select name="riesgo" class="form-select">
                                <option value="Bajo">🟢 Bajo (No detiene proceso)</option>
                                <option value="Medio">🟡 Medio (Atención requerida)</option>
                                <option value="Alto">🔴 Alto (Peligro Inminente)</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">2. Clasificación del Evento</div>
                    <div class="mb-3">
                        <label>Tipo de Hallazgo</label>
                        <select name="hallazgo" id="tipoHallazgo" class="form-select" onchange="cargarCausas()" required>
                            <option value="">Seleccione...</option>
                            <option value="Acto Subestándar">⚠️ Acto Subestándar (Comportamiento)</option>
                            <option value="Condición Subestándar">🔧 Condición Subestándar (Entorno)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Clasificación Específica</label>
                        <select name="causa_especifica" id="causaEspecifica" class="form-select" required>
                            <option value="">Primero seleccione tipo...</option>
                        </select>
                    </div>

                    <div class="section-title">3. Descripción y Evidencia</div>
                    
                    <div class="mb-3">
                        <label>Descripción del Evento</label>
                        <textarea name="descripcion" class="form-control" rows="3" required placeholder="Describe qué observaste..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Acción Inmediata Tomada (Opcional)</label>
                        <textarea name="accion_inmediata" class="form-control" rows="2" placeholder="¿Hiciste algo para corregirlo en el momento? Ej: Se colocó cinta de peligro, se habló con el operador..."></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label>Aviso SAP (Si aplica)</label>
                            <input type="text" name="sap" class="form-control" placeholder="Ej: 10002456">
                        </div>
                        <div class="col-md-6">
                            <label>¿Se detuvo actividad?</label>
                            <select name="detuvo" class="form-select">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="d-block mb-2">Evidencia Fotográfica</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" capture="environment">
                        <div class="form-text">Toma una foto clara del acto o condición.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-submit btn-lg">ENVIAR REPORTE</button>
                    </div>

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

            const actos = ["No uso de EPP", "Operar sin autorización", "Uso incorrecto de herramientas", "Posición inadecuada", "Bromas / Juegos", "Exceso de velocidad", "Omitir señalización"];
            const condiciones = ["Orden y Limpieza deficiente", "Herramientas defectuosas", "Ruido excesivo", "Iluminación deficiente", "Falta de señalización", "Piso resbaloso/irregular", "Riesgo de incendio"];

            let opciones = (tipo === "Acto Subestándar") ? actos : condiciones;
            
            opciones.forEach(op => {
                let option = document.createElement("option");
                option.text = op;
                option.value = op;
                select.add(option);
            });
        }
    </script>
</body>
</html>
