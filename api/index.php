<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: sans-serif; }
        .header { background: #0f172a; color: white; padding: 30px 0; margin-bottom: 30px; border-bottom: 5px solid #2563eb; text-align: center; }
        .card-form { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .section-title { color: #2563eb; font-weight: 800; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin: 25px 0 15px 0; text-transform: uppercase; font-size: 0.9rem; }
        .btn-submit { background: #0f172a; color: white; width: 100%; padding: 15px; font-weight: bold; border: none; border-radius: 5px; margin-top: 20px; }
        .btn-submit:hover { background: #1e293b; }
        .action-box { background: #eff6ff; border: 1px solid #bfdbfe; padding: 15px; border-radius: 5px; border-left: 5px solid #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="fw-bold m-0">REPORTE DE SEGURIDAD</h2>
        <small style="opacity:0.8; letter-spacing:1px;">SISTEMA DE GESTIÓN SST - VITAPRO</small>
    </div>

    <div class="container pb-5">
        <form action="api/guardar.php" method="POST" enctype="multipart/form-data" class="card-form">
            
            <div class="section-title" style="margin-top:0;">1. Información General</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Reportado Por</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Nombre completo">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Área</label>
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
                    <label class="form-label fw-bold">Tipo Usuario</label>
                    <select name="tipo_usuario" class="form-select">
                        <option value="Interno">Interno</option>
                        <option value="Contratista">Contratista</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Empresa (Opcional)</label>
                    <input type="text" name="empresa" class="form-control">
                </div>
            </div>

            <div class="section-title">2. Detalle del Hallazgo</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo</label>
                    <select name="hallazgo" class="form-select" required>
                        <option value="Acto Subestándar">Acto Subestándar</option>
                        <option value="Condición Subestándar">Condición Subestándar</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Causa / Clasificación</label>
                    <select name="causa_especifica" class="form-select">
                        <option value="General">General</option>
                        <option value="EPP">Falta de EPP</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Procedimiento">Incumplimiento Proc.</option>
                        <option value="Orden">Orden y Limpieza</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2" required placeholder="¿Qué sucedió?"></textarea>
                </div>
            </div>

            <div class="section-title">3. Gestión Inmediata</div>
            <div class="action-box mb-3">
                <label class="form-label fw-bold text-primary">✅ Acción Correctiva Inmediata</label>
                <textarea name="accion_inmediata" class="form-control" rows="2" required placeholder="¿Qué hizo usted para controlar el riesgo? Ej: Se detuvo equipo, se colocó cinta..."></textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Riesgo</label>
                    <select name="riesgo" class="form-select">
                        <option value="Bajo">Bajo</option>
                        <option value="Medio">Medio</option>
                        <option value="Alto">Alto</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">¿Detuvo actividad?</label>
                    <select name="detuvo" class="form-select">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Aviso SAP</label>
                    <input type="text" name="sap" class="form-control">
                </div>
            </div>

            <div class="section-title">4. Evidencia</div>
            <input type="file" name="foto" class="form-control" accept="image/*">

            <button type="submit" class="btn-submit">ENVIAR REPORTE</button>
        </form>
    </div>
</body>
</html>
