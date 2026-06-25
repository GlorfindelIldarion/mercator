import {
    Cell,
    CellEditorHandler,
    type CellStateStyle,
    EventObject,
    FitPlugin,
    Geometry,
    Graph,
    GraphDataModel,
    type GraphPluginConstructor,
    InternalEvent,
    type InternalMouseEvent,
    ModelXmlSerializer,
    PanningHandler,
    Point,
    RubberBandHandler,
    SelectionCellsHandler,
    SelectionHandler,
    styleUtils,
    type UndoableEdit,
    UndoManager,
    VertexHandler,
    VertexHandlerConfig,
} from '@maxgraph/core';

//-----------------------------------------------------------------------
// Interfaces métier
// Note: "Node" est volontairement renommé MapNode pour ne pas masquer le DOM Node.

interface Edge {
    attachedNodeId: string;
    name: string;
    edgeType: string;
    edgeDirection: string;
    bidirectional: boolean;
    color: string | null;
}

interface MapNode {
    id: string;
    vue: string;
    label: string;
    title?: string;
    attributes?: string | null;
    order: number;
    image: string;
    type: string;
    edges: Edge[];
}

type NodeMap = Map<string, MapNode>;

// Déclaration de globals fournies par ailleurs
declare const _nodes: NodeMap;

// Drapeaux métier ajoutés au style MaxGraph (absents de CellStateStyle).
interface AppCellStyle extends CellStateStyle {
    isBackground?: boolean;
    isRectangle?: boolean;
}

function styleOf(cell: Cell | null | undefined): AppCellStyle | undefined {
    return cell?.style as AppCellStyle | undefined;
}

function isBackgroundCell(cell: Cell | null | undefined): boolean {
    return !!styleOf(cell)?.isBackground;
}

function isRectangleCell(cell: Cell | null | undefined): boolean {
    return !!styleOf(cell)?.isRectangle;
}

//-----------------------------------------------------------------------
// Plugins MaxGraph

const plugins: GraphPluginConstructor[] = [
    // L'ordre est important
    CellEditorHandler,
    SelectionCellsHandler,
    SelectionHandler,
    PanningHandler,
    RubberBandHandler,
    FitPlugin,
];

// Initialisation du graph

const container = document.getElementById('graph-container') as HTMLDivElement | null;
if (!container) {
    throw new Error('#graph-container introuvable');
}

const graph = new Graph(container, new GraphDataModel(), plugins);
const model = graph.getDataModel();

//-----------------------------------------------------------------------
// Style des arêtes

const edgeDefaultStyle = graph.getStylesheet().getDefaultEdgeStyle();
edgeDefaultStyle.labelBackgroundColor = '#FFFFFF';
edgeDefaultStyle.strokeWidth = 2;
edgeDefaultStyle.rounded = false;
edgeDefaultStyle.entryPerimeter = false;

// Désactiver le folding
graph.getFoldingImage = () => null;

// Verrouillage de la cellule d'arrière-plan
const _isSelectable = graph.isCellSelectable.bind(graph);
graph.isCellSelectable = (cell) => !isBackgroundCell(cell) && _isSelectable(cell);
const _isMovable = graph.isCellMovable.bind(graph);
graph.isCellMovable = (cell) => !isBackgroundCell(cell) && _isMovable(cell);

// La taille d'un groupe ne doit jamais être modifiée manuellement (elle est
// calculée à la création et doit rester cohérente avec son contenu).
const _isResizable = graph.isCellResizable.bind(graph);
graph.isCellResizable = (cell) => (cell?.children?.length ?? 0) === 0 && _isResizable(cell);

// Sélection des sommets
VertexHandlerConfig.selectionColor = '#00a8ff';
VertexHandlerConfig.selectionStrokeWidth = 2;

// Resize uniquement par les coins (largeur et hauteur modifiées ensemble) :
// on masque les points d'accroche N/S/E/W, ne gardant que NW/NE/SW/SE
// (indices 0, 2, 5 et 7 dans l'ordre de création des sizers). isSizerVisible
// est appelée depuis le constructeur de VertexHandler : la surcharger sur
// l'instance après coup est trop tard, il faut sous-classer.
const RESIZE_CORNER_INDICES = new Set([0, 2, 5, 7]);

class CornerOnlyVertexHandler extends VertexHandler {
    isSizerVisible(index: number): boolean {
        return RESIZE_CORNER_INDICES.has(index);
    }

    // Le resize doit toujours conserver le ratio largeur/hauteur, pas
    // seulement quand Shift est maintenu — sauf pour les rectangles, qui
    // doivent pouvoir être étirés librement dans chaque direction.
    isConstrainedEvent(me: InternalMouseEvent): boolean {
        if (isRectangleCell(this.state.cell)) {
            return super.isConstrainedEvent(me);
        }
        return true;
    }
}

graph.createVertexHandler = (state) => new CornerOnlyVertexHandler(state);

//-----------------------------------------------------------------------
// Undo / Redo

const undoManager = new UndoManager();
let physicsSuppressUndo = false;
const undoListener = (_sender: unknown, evt: EventObject) => {
    if (physicsSuppressUndo) return;
    const edit = evt.getProperty('edit') as UndoableEdit | undefined;
    if (edit) {
        undoManager.undoableEditHappened(edit);
    }
};

model.addListener(InternalEvent.UNDO, undoListener);
graph.getView().addListener(InternalEvent.UNDO, undoListener);

const undoButton = document.getElementById('undoButton') as HTMLButtonElement | null;
const redoButton = document.getElementById('redoButton') as HTMLButtonElement | null;

if (undoButton) {
    undoButton.addEventListener('click', () => {
        if (undoManager.canUndo()) undoManager.undo();
    });
}

if (redoButton) {
    redoButton.addEventListener('click', () => {
        if (undoManager.canRedo()) undoManager.redo();
    });
}

document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.ctrlKey && event.key === 'z') {
        event.preventDefault();
        if (undoManager.canUndo()) undoManager.undo();
    } else if (event.ctrlKey && event.key === 'y') {
        event.preventDefault();
        if (undoManager.canRedo()) undoManager.redo();
    }
});

// --------------------------------------------------------------------------------
// Menus contextuels

const MENU_OFFSET_X = 75;
const MENU_OFFSET_Y = 100;

const edgeContextMenu = document.getElementById('edge-context-menu') as HTMLDivElement | null;
const edgeColorSelect = document.getElementById('edge-color-select') as HTMLInputElement | null;
const thicknessSelect = document.getElementById('edge-thickness-select') as HTMLSelectElement | null;

const textContextMenu = document.getElementById('text-context-menu') as HTMLDivElement | null;
const textFontSelect = document.getElementById('text-font-select') as HTMLSelectElement | null;
const textColorSelect = document.getElementById('text-color-select') as HTMLInputElement | null;
const textSizeSelect = document.getElementById('text-size-select') as HTMLSelectElement | null;
const textBoldSelect = document.getElementById('text-bold-select') as HTMLButtonElement | null;
const textItalicSelect = document.getElementById('text-italic-select') as HTMLButtonElement | null;
const textUnderlineSelect = document.getElementById('text-underline-select') as HTMLButtonElement | null;

let selectedCell: Cell | null = null;

function hideContextMenus(): void {
    if (textContextMenu) textContextMenu.style.display = 'none';
    if (edgeContextMenu) edgeContextMenu.style.display = 'none';
}

function showEdgeMenu(x: number, y: number, style: CellStateStyle): void {
    if (!edgeContextMenu || !textContextMenu) return;
    edgeContextMenu.style.display = 'block';
    edgeContextMenu.style.left = `${x + MENU_OFFSET_X}px`;
    edgeContextMenu.style.top = `${y + MENU_OFFSET_Y}px`;
    if (edgeColorSelect) edgeColorSelect.value = style.strokeColor ?? '#000000';
    if (thicknessSelect) thicknessSelect.value = String(style.strokeWidth ?? '1');
    textContextMenu.style.display = 'none';
}

function showTextMenu(x: number, y: number, style: CellStateStyle, cellStyle: AppCellStyle | undefined): void {
    if (!edgeContextMenu || !textContextMenu) return;
    textContextMenu.style.display = 'block';
    textContextMenu.style.left = `${x + MENU_OFFSET_X}px`;
    textContextMenu.style.top = `${y + MENU_OFFSET_Y}px`;
    if (textColorSelect) textColorSelect.value = style.fontColor ?? '#000000';
    if (textFontSelect) textFontSelect.value = style.fontFamily ?? 'Arial';
    if (textSizeSelect) textSizeSelect.value = String(style.fontSize ?? '12');
    edgeContextMenu.style.display = 'none';

    const fontStyle = cellStyle?.fontStyle ?? 0;
    textBoldSelect?.classList.toggle('selected', !!(fontStyle & 1));
    textItalicSelect?.classList.toggle('selected', !!(fontStyle & 2));
    textUnderlineSelect?.classList.toggle('selected', !!(fontStyle & 4));
}

graph.container.addEventListener('contextmenu', (event: MouseEvent) => {
    event.preventDefault();

    const cell = graph.getCellAt(event.offsetX, event.offsetY) as Cell | null;
    if (!cell) return;

    if (isBackgroundCell(cell)) {
        hideContextMenus();
        return;
    }

    const rect = container.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const currentStyle = graph.getCellStyle(cell);

    if (cell.isEdge()) {
        selectedCell = cell;
        showEdgeMenu(x, y, currentStyle);
    } else if (cell.isVertex()) {
        const cellValue = cell.value as string | null;
        const hasText = !!cellValue && cellValue.trim() !== '';
        const cellStyle = styleOf(cell);

        if (hasText && textColorSelect && textFontSelect && textSizeSelect) {
            selectedCell = cell;
            showTextMenu(x, y, currentStyle, cellStyle);
        } else if (!cellStyle?.image && (!cell.children || cell.children.length === 0)) {
            selectedCell = cell;
            showEdgeMenu(x, y, currentStyle);
        } else {
            hideContextMenus();
        }
    } else {
        hideContextMenus();
    }
});

document.getElementById('apply-edge-style')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!selectedCell || !edgeColorSelect || !thicknessSelect) return;

    graph.batchUpdate(() => {
        const style: CellStateStyle = selectedCell!.style ?? {};
        const thickness = parseInt(thicknessSelect!.value, 10) || 1;

        if (selectedCell!.isEdge()) {
            style.strokeColor = edgeColorSelect!.value;
        } else {
            style.fillColor = edgeColorSelect!.value;
        }
        style.strokeWidth = thickness;
        selectedCell!.style = style;
        graph.refresh(selectedCell!);
    });

    if (edgeContextMenu) edgeContextMenu.style.display = 'none';
});

document.getElementById('apply-text-style')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!selectedCell || !textFontSelect || !textColorSelect || !textSizeSelect) return;

    graph.batchUpdate(() => {
        const style: CellStateStyle = selectedCell!.style ?? {};

        style.fontFamily = textFontSelect!.value;
        style.fontColor = textColorSelect!.value;
        style.fontSize = parseInt(textSizeSelect!.value, 10) || 12;

        let flag = 0;
        if (textBoldSelect?.classList.contains('selected')) flag |= 1;
        if (textItalicSelect?.classList.contains('selected')) flag |= 2;
        if (textUnderlineSelect?.classList.contains('selected')) flag |= 4;
        style.fontStyle = flag;

        selectedCell!.style = style;
        graph.refresh(selectedCell!);
    });

    if (textContextMenu) textContextMenu.style.display = 'none';
});

// Boutons avec classe .button → toggle "selected"
document
    .querySelectorAll<HTMLButtonElement>('.button')
    .forEach((button) => {
        button.addEventListener('click', () => button.classList.toggle('selected'));
    });

// Cacher les menus contextuels en cliquant ailleurs
document.addEventListener('click', (event) => {
    const target = event.target as globalThis.Node | null;
    if (textContextMenu && !textContextMenu.contains(target)) textContextMenu.style.display = 'none';
    if (edgeContextMenu && !edgeContextMenu.contains(target)) edgeContextMenu.style.display = 'none';
});

// --------------------------------------------------------------------------------
// Désactive la grille

graph.setGridEnabled(false);

// -----------------------------------------------------------------------
// Panning

graph.setPanning(true);
graph.allowAutoPanning = true;
graph.useScrollbarsForPanning = true;

//-------------------------------------------------------------------------
// LOAD / SAVE

export function loadGraph(xml: string) {
    new ModelXmlSerializer(model).import(xml);
    refreshParallelEdges();
    // Cadre la totalité des objets dans l'écran, aligné à gauche
    graph.getPlugin<FitPlugin>('fit')?.fit({margin: 20});
}

(window as any).loadGraph = loadGraph;

async function saveGraphToDatabase(
    id: number | string,
    name: string,
    type: string,
    content: string,
): Promise<void> {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    const csrfToken = csrfMeta?.content;
    if (!csrfToken) {
        console.error('CSRF token manquant');
        alert('Token CSRF manquant.');
        return;
    }

    try {
        const response = await fetch(`/admin/graphs/${id}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({_method: 'PUT', id, name, type, content}),
        });

        if (response.status !== 200) {
            let errorMessage = 'Erreur lors de la sauvegarde du graphe.';
            try {
                const error = await response.json();
                if (error?.message) errorMessage = error.message;
            } catch { /* ignore */
            }
            throw new Error(errorMessage);
        }
    } catch (error) {
        console.error('Erreur lors de la sauvegarde :', error);
        alert('Erreur lors de la sauvegarde du graphe.');
    }
}

function getXMLGraph(): string {
    return new ModelXmlSerializer(graph.getDataModel()).export();
}

(window as any).getXMLGraph = getXMLGraph;

function saveGraph() {
    const idInput = document.querySelector('#id') as HTMLInputElement | null;
    const nameInput = document.querySelector('#name') as HTMLInputElement | null;
    const typeInput = document.querySelector('#type') as HTMLInputElement | null;

    if (!idInput || !nameInput || !typeInput) {
        alert('Champs id / name / type manquants');
        return;
    }

    saveGraphToDatabase(idInput.value, nameInput.value, typeInput.value, new ModelXmlSerializer(model).export());
}

document.getElementById('saveButton')?.addEventListener('click', saveGraph);

//-------------------------------------------------------------------------
// Utilitaires

type Pt = { x: number; y: number };

function getGraphPointFromEvent(graph: Graph, evt: MouseEvent | DragEvent): Pt {
    const pt = graph.getPointForEvent(evt);
    return {x: pt.x, y: pt.y};
}

function getFilter(): string[] {
    const select = document.getElementById('filters') as HTMLSelectElement | null;
    if (!select) return [];
    return Array.from(select.options).filter(o => o.selected).map(o => o.value);
}

function getAttrFilter(): string[] {
    const select = document.getElementById('attr-filter') as HTMLSelectElement | null;
    if (!select) return [];
    return Array.from(select.options).filter(o => o.selected).map(o => o.value);
}

function matchesAttrFilter(node: MapNode, attrFilter: string[]): boolean {
    if (attrFilter.length === 0) return true;
    if (!node.attributes) return false;
    const nodeAttrs = node.attributes.split(' ').map(s => s.trim()).filter(Boolean);
    return attrFilter.some(a => nodeAttrs.includes(a));
}

// 1 = amont (up), 2 = aval (down), 3 = les deux
function getDirection(): number {
    if ((document.getElementById('direction-up') as HTMLInputElement | null)?.checked) return 1;
    if ((document.getElementById('direction-down') as HTMLInputElement | null)?.checked) return 2;
    return 3;
}

function matchesDirection(direction: number, source: MapNode, target: MapNode): boolean {
    if (direction === 1 && source.order <= target.order) return false;
    if (direction === 2 && source.order >= target.order) return false;
    return true;
}

//-------------------------------------------------------------------------
// Affichage IP / tags dans les labels

function getShowIP(): boolean {
    return document.getElementById('toggleIP')?.classList.contains('active') ?? false;
}

function getShowAttr(): boolean {
    return document.getElementById('toggleAttr')?.classList.contains('active') ?? false;
}

function buildLabel(node: MapNode): string {
    if (getShowIP() && node.title) return node.label + '\n' + node.title;
    if (getShowAttr() && node.attributes) return node.label + '\n' + node.attributes;
    return node.label;
}

function refreshNodeLabels(): void {
    graph.batchUpdate(() => {
        for (const cell of collectVertices()) {
            const style = styleOf(cell);
            if (!style?.image || style.isBackground) continue;
            const node = _nodes.get(cell.id as string);
            if (!node) continue;
            cell.value = buildLabel(node);
        }
    });
    graph.refresh();
}

// Désactivés par défaut à chaque chargement de la page (pas de persistance).
function setToggleActive(btn: HTMLElement | null, active: boolean): void {
    if (!btn) return;
    btn.classList.toggle('active', active);
    btn.setAttribute('aria-pressed', String(active));
}

setToggleActive(document.getElementById('toggleIP'), false);
setToggleActive(document.getElementById('toggleAttr'), false);

document.getElementById('toggleIP')?.addEventListener('click', (e) => {
    const btn = e.currentTarget as HTMLElement;
    const isActive = !btn.classList.contains('active');
    setToggleActive(btn, isActive);
    if (isActive) setToggleActive(document.getElementById('toggleAttr'), false);
    refreshNodeLabels();
});

document.getElementById('toggleAttr')?.addEventListener('click', (e) => {
    const btn = e.currentTarget as HTMLElement;
    const isActive = !btn.classList.contains('active');
    setToggleActive(btn, isActive);
    if (isActive) setToggleActive(document.getElementById('toggleIP'), false);
    refreshNodeLabels();
});

// Nombre de liens déjà tracés entre deux sommets (quel que soit leur sens)
function countEdgesBetween(src: Cell, dest: Cell): number {
    return graph.getEdges(src).filter((e) => e.source === dest || e.target === dest).length;
}

// Ajoute tous les liens manquants entre objets déjà présents sur le canevas,
// d'après les données de _nodes (pas seulement ceux du nœud double-cliqué).
function completeMissingEdgesAmongPlacedNodes(parent: Cell): void {
    const placedIds = new Set(collectVertices().filter(isMovableObject).map((v) => String(v.id)));

    for (const id of placedIds) {
        const sourceNode = _nodes.get(id);
        const sourceCell = model.getCell(id) as Cell | null;
        if (!sourceNode || !sourceCell) continue;

        const edgesByTarget = new Map<string, Edge[]>();
        for (const edge of sourceNode.edges) {
            if (!placedIds.has(edge.attachedNodeId)) continue;
            const list = edgesByTarget.get(edge.attachedNodeId);
            if (list) list.push(edge); else edgesByTarget.set(edge.attachedNodeId, [edge]);
        }

        for (const [targetId, edges] of edgesByTarget) {
            const targetCell = model.getCell(targetId) as Cell | null;
            if (!targetCell) continue;
            const alreadyDrawn = countEdgesBetween(sourceCell, targetCell);
            for (const edge of edges.slice(alreadyDrawn)) {
                graph.insertEdge({
                    parent,
                    value: edge.name,
                    source: sourceCell,
                    target: targetCell,
                    style: buildEdgeStyle(edge),
                });
            }
        }
    }
}

function modelCenter(cell: Cell): Point {
    const g = cell.getGeometry();
    const w = g?.width ?? 0, h = g?.height ?? 0;
    let x = 0, y = 0;
    let c: Cell | null = cell;
    const root = graph.getDefaultParent();
    while (c && c !== root) {
        const cg = c.getGeometry();
        if (cg && !c.isEdge()) {
            x += cg.x;
            y += cg.y;
        }
        c = c.getParent();
    }
    return new Point(x + w / 2, y + h / 2);
}

function refreshParallelEdges(): void {
    const parent = graph.getDefaultParent();
    const edges = graph.getChildEdges(parent);

    const groups = new Map<string, Cell[]>();
    for (const edge of edges) {
        const s = edge.source, t = edge.target;
        if (!s || !t) continue;
        const a = String(s.id), b = String(t.id);
        const key = a < b ? `${a}|${b}` : `${b}|${a}`;
        const list = groups.get(key);
        if (list) list.push(edge); else groups.set(key, [edge]);
    }

    graph.batchUpdate(() => {
        for (const list of groups.values()) {
            const n = list.length;
            list.forEach((edge, i) => {
                const geo = edge.getGeometry()?.clone();
                if (!geo) return;
                const style: CellStateStyle = edge.style ?? {};
                if (n <= 1) {
                    geo.points = null;
                    style.curved = false;
                } else {
                    const cs = modelCenter(edge.source!), ct = modelCenter(edge.target!);
                    const mx = (cs.x + ct.x) / 2, my = (cs.y + ct.y) / 2;
                    const dx = ct.x - cs.x, dy = ct.y - cs.y;
                    const len = Math.hypot(dx, dy) || 1;
                    const nx = -dy / len, ny = dx / len;
                    const offset = (i - (n - 1) / 2) * 22;
                    geo.points = [new Point(mx + nx * offset, my + ny * offset)];
                    style.curved = true;
                }
                edge.style = style;
                edge.setGeometry(geo);
            });
        }
    });
    graph.refresh();
}

function buildEdgeStyle(edge: Edge): CellStateStyle {
    const isFlux = edge.edgeType === 'FLUX';
    const isCable = edge.edgeType === 'CABLE';
    const isLink = edge.edgeType === 'LINK';
    return {
        editable: false,
        // Un lien physique (CABLE) garde la couleur définie dans le JSON.
        strokeColor: isCable ? (edge.color ?? '#000000') : '#000000',
        strokeWidth: isCable ? 3 : 2,
        // Comme dans l'explorateur : appartenance (LINK) en pointillé, flux en trait plein.
        dashed: isLink,
        startArrow: isFlux && (edge.bidirectional || edge.edgeDirection === 'FROM') ? 'classic' : 'none',
        endArrow: isFlux && (edge.bidirectional || edge.edgeDirection === 'TO') ? 'classic' : 'none',
    };
}

graph.enterStopsCellEditing = true;

//-------------------------------------------------------------------------
// Drag & drop (gestionnaire unique)

const fontBtn = document.getElementById('font-btn') as HTMLElement | null;
const squareIcon = document.getElementById('square-btn') as HTMLElement | null;
const nodeIcon = document.getElementById('nodeImage') as HTMLImageElement | null;
const nodeSelector = document.getElementById('node') as HTMLSelectElement | null;

fontBtn?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'text-node'));
squareIcon?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'square-node'));
nodeIcon?.addEventListener('dragstart', (e: DragEvent) => e.dataTransfer?.setData('node-type', 'icon-node'));

container.addEventListener('dragover', (event: DragEvent) => event.preventDefault());

container.addEventListener('drop', (event: DragEvent) => {
    event.preventDefault();
    const type = event.dataTransfer?.getData('node-type');
    if (!type) return;

    const pt = getGraphPointFromEvent(graph, event);
    const parent = graph.getDefaultParent();

    if (type === 'text-node') {
        graph.batchUpdate(() => {
            const vertex = graph.insertVertex({
                parent,
                value: 'Text',
                position: [pt.x, pt.y],
                size: [150, 30],
                style: {
                    fillColor: 'none',
                    strokeColor: 'none',
                    fontColor: '#000000',
                    fontSize: 14,
                    align: 'left',
                    // 'top' (plutôt que 'middle') évite tout décalage pendant
                    // l'édition : l'éditeur de MaxGraph applique une translation
                    // CSS en pourcentage de sa propre hauteur pour un alignement
                    // 'middle', hauteur qui varie légèrement tant que le texte
                    // n'a pas encore été redimensionné côté modèle.
                    verticalAlign: 'top',
                    // Recalcule automatiquement la taille de la cellule à partir
                    // du texte rendu dès la fin de l'édition (labelChanged).
                    autoSize: true,
                },
            });
            graph.setSelectionCell(vertex);
        });
        return;
    }

    if (type === 'square-node') {
        graph.batchUpdate(() => {
            const vertex = graph.insertVertex({
                parent,
                value: '',
                position: [pt.x, pt.y],
                size: [150, 120],
                style: {
                    fillColor: '#fffacd',
                    strokeColor: '#000000',
                    strokeWidth: 1,
                    rounded: true,
                    isRectangle: true,
                } as AppCellStyle,
            });
            graph.orderCells(true, [vertex]);
            graph.setSelectionCell(vertex);
        });
        return;
    }

    if (type === 'icon-node' && nodeIcon?.src && nodeSelector) {
        const nodeId = nodeSelector.value;
        graph.batchUpdate(() => {
            const existing = model.getCell(nodeId) as Cell | null;
            if (existing) {
                graph.setSelectionCells([existing]);
                return;
            }

            const node = _nodes.get(nodeId);
            if (!node) return;

            const newVertex = graph.insertVertex({
                parent,
                id: nodeId,
                value: buildLabel(node),
                position: [pt.x - 16, pt.y - 16],
                size: [32, 32],
                style: {
                    shape: 'image',
                    image: nodeIcon.src,
                    editable: false,
                    resizable: true,
                    verticalLabelPosition: 'bottom',
                    spacingTop: -15,
                },
            });

            node.edges.forEach((edge) => {
                const targetCell = model.getCell(edge.attachedNodeId) as Cell | null;
                if (targetCell) {
                    graph.insertEdge({
                        parent,
                        value: '',
                        source: newVertex,
                        target: targetCell,
                        style: {
                            editable: false,
                            strokeColor: '#ff0000',
                            strokeWidth: 2,
                            startArrow: 'none',
                            endArrow: 'none',
                        },
                    });
                }
            });

            graph.setSelectionCell(newVertex);
        });
        refreshParallelEdges();
    }
});

//-------------------------------------------------------------------------
// Zoom

const zoomInButton = document.getElementById('zoom-in-btn') as HTMLButtonElement | null;
const zoomOutButton = document.getElementById('zoom-out-btn') as HTMLButtonElement | null;

if (zoomInButton) zoomInButton.addEventListener('click', () => graph.zoomIn());
if (zoomOutButton) zoomOutButton.addEventListener('click', () => graph.zoomOut());

//-------------------------------------------------------------------------
// Suppression avec Delete / Backspace ou le bouton "Delete"

function deleteSelectedCells(): void {
    const cells = graph.getSelectionCells().filter((c) => !isBackgroundCell(c));
    if (cells.length === 0) return;

    graph.removeCells(cells);
    refreshParallelEdges();
}

document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key !== 'Delete' && event.key !== 'Backspace') return;

    // Laisser le navigateur gérer Delete/Backspace dans un champ de saisie
    // normal (nom, type, etc.) : on ne s'occupe que de la sélection du graphe.
    const target = event.target as HTMLElement | null;
    if (target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable) return;

    if (graph.getSelectionCells().filter((c) => !isBackgroundCell(c)).length === 0) return;

    // Empêche Backspace de déclencher la navigation "retour" du navigateur
    // et bloque la propagation vers d'autres gestionnaires de la page.
    event.preventDefault();
    event.stopPropagation();

    deleteSelectedCells();
});

document.getElementById('delete-btn')?.addEventListener('click', deleteSelectedCells);

//-------------------------------------------------------------------------
// CTRL+A : sélectionner tout

document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.ctrlKey && event.key === 'a') {
        event.preventDefault();
        event.stopPropagation();
        graph.selectAll();
    }
});

//-------------------------------------------------------------------------
// Connexions / déconnexions

graph.setConnectable(false);
graph.isCellDisconnectable = () => false;

//-------------------------------------------------------------------------
// Group / ungroup

const groupButton = document.getElementById('group-btn') as HTMLButtonElement | null;
const ungroupButton = document.getElementById('ungroup-btn') as HTMLButtonElement | null;

// Un groupe contenant un rectangle doit rester en arrière-plan par rapport
// aux flèches qui ne font pas partie du groupe.
function groupContainsRectangle(group: Cell): boolean {
    return (group.children ?? []).some((c) => isRectangleCell(c));
}

if (groupButton) {
    groupButton.addEventListener('click', () => {
        const cells = graph.getSelectionCells().filter((c) => !isBackgroundCell(c));
        if (cells.length > 1) {
            const parent = graph.getDefaultParent();

            // MaxGraph peut redimensionner un enfant lors de son ajout au groupe
            // (constrainChild s'applique avant que le groupe n'ait sa taille finale).
            // On mémorise la géométrie d'origine pour la restaurer après coup.
            const originalGeometries = new Map<Cell, Geometry>();
            for (const cell of cells) {
                const geo = cell.getGeometry();
                if (geo) originalGeometries.set(cell, geo.clone());
            }

            graph.batchUpdate(() => {
                // insertVertex ajoute déjà le groupe au parent — pas besoin de addCell()
                const group = graph.insertVertex({
                    parent,
                    style: {fillColor: 'none', strokeColor: 'none', resizable: false},
                });
                graph.groupCells(group, 5, cells);

                const groupGeo = group.getGeometry();
                if (!groupGeo) return;

                for (const cell of cells) {
                    const original = originalGeometries.get(cell);
                    if (!original) continue;
                    model.setGeometry(cell, new Geometry(
                        original.x - groupGeo.x,
                        original.y - groupGeo.y,
                        original.width,
                        original.height,
                    ));
                }

                // Les rectangles du groupe doivent rester en arrière-plan par
                // rapport à ses nœuds (au sein du groupe) et aux liens qui ne
                // font pas partie du groupe (au niveau du canevas).
                const rectanglesInGroup = (group.children ?? []).filter((c) => isRectangleCell(c));
                if (rectanglesInGroup.length > 0) {
                    graph.orderCells(true, rectanglesInGroup);
                    graph.orderCells(true, [group]);
                }

                graph.refresh();
                graph.setSelectionCell(group);
            });
        }
    });
}

if (ungroupButton) {
    ungroupButton.addEventListener('click', () => {
        const cells = graph.getSelectionCells();
        if (cells.length === 1) {
            const released = graph.ungroupCells(cells);

            // Les rectangles libérés doivent rester en arrière-plan par
            // rapport aux flèches qui ne faisaient pas partie du groupe.
            const rectangles = released.filter((c) => isRectangleCell(c));
            if (rectangles.length > 0) {
                graph.orderCells(true, rectangles);
            }

            graph.setSelectionCells(released);
        }
    });
}

// Déplacer un groupe peut le faire remonter au-dessus des flèches externes :
// on réapplique l'arrière-plan pour les groupes contenant un rectangle.
graph.addListener(InternalEvent.MOVE_CELLS, (_sender: unknown, evt: EventObject) => {
    const cells = evt.getProperty('cells') as Cell[] | undefined;
    if (!cells) return;

    const groups = cells.filter((c) => c.isVertex() && (c.children?.length ?? 0) > 0 && groupContainsRectangle(c));
    if (groups.length > 0) {
        graph.orderCells(true, groups);
    }
});

//---------------------------------------------------------------------------
// Déplacement avec flèches

function moveSelectedVertices(graph: Graph, dx: number, dy: number): void {
    const vertices = graph.getSelectionCells().filter((c) => c.isVertex() && !isBackgroundCell(c));
    if (vertices.length === 0) return;

    graph.batchUpdate(() => {
        for (const vertex of vertices) {
            vertex.getGeometry()?.translate(dx, dy);
        }
        graph.refresh();
    });
}

document.addEventListener('keydown', (event: KeyboardEvent) => {
    const step = 1;
    switch (event.key) {
        case 'ArrowUp':
            moveSelectedVertices(graph, 0, -step);
            break;
        case 'ArrowDown':
            moveSelectedVertices(graph, 0, step);
            break;
        case 'ArrowLeft':
            moveSelectedVertices(graph, -step, 0);
            break;
        case 'ArrowRight':
            moveSelectedVertices(graph, step, 0);
            break;
    }
});

//---------------------------------------------------------------------------
// Placement en cercle (double-clic)

function placeObjectsOnCircle(center: Pt, radius: number, n: number): Pt[] {
    const angleStep = (2 * Math.PI) / n;
    return Array.from({length: n}, (_, i) => ({
        x: center.x + radius * Math.cos(i * angleStep),
        y: center.y + radius * Math.sin(i * angleStep),
    }));
}

//----------------------------------------------------------------
// Double-clic sur icône

graph.addListener(InternalEvent.DOUBLE_CLICK, (_sender: unknown, evt: EventObject) => {
    const cell = evt.getProperty('cell') as Cell | null;
    if (!cell?.isVertex()) return;

    const style = cell.style;
    if (style?.shape !== 'image') return;

    const node = _nodes.get(cell.id as string);
    if (!node) return;

    graph.batchUpdate(() => {
        // Plusieurs liens peuvent exister entre les deux mêmes nœuds : on les
        // regroupe par cible pour n'insérer le sommet qu'une fois tout en
        // conservant chacun des liens (refreshParallelEdges les écartera en arc).
        const newEdgesByTarget = new Map<string, Edge[]>();
        const parent = graph.getDefaultParent();
        const filter = getFilter();
        const attrFilter = getAttrFilter();
        const direction = getDirection();

        node.edges.forEach((edge) => {
            const targetNode = _nodes.get(edge.attachedNodeId);
            if (!targetNode) return;
            if (model.getCell(edge.attachedNodeId)) return; // déjà présent : traité par la passe globale ci-dessous

            if (
                (
                    filter.length === 0 ||
                    filter.includes(targetNode.vue) ||
                    (filter.includes('8') && edge.edgeType === 'CABLE') ||
                    (filter.includes('9') && edge.edgeType === 'FLUX')
                ) &&
                matchesAttrFilter(targetNode, attrFilter) &&
                matchesDirection(direction, node, targetNode)
            ) {
                const list = newEdgesByTarget.get(edge.attachedNodeId);
                if (list) list.push(edge); else newEdgesByTarget.set(edge.attachedNodeId, [edge]);
            }
        });

        const geom = cell.getGeometry();
        if (geom && newEdgesByTarget.size > 0) {
            const targetIds = Array.from(newEdgesByTarget.keys());
            const positions = placeObjectsOnCircle({x: geom.x, y: geom.y}, 80, targetIds.length);

            for (let i = 0; i < positions.length; i++) {
                const attachedNodeId = targetIds[i];
                const edgesToTarget = newEdgesByTarget.get(attachedNodeId)!;
                const newNode = _nodes.get(attachedNodeId);
                if (!newNode) continue;

                const vertex = graph.insertVertex({
                    parent,
                    id: newNode.id,
                    value: buildLabel(newNode),
                    position: [positions[i].x, positions[i].y],
                    size: [32, 32],
                    style: {
                        shape: 'image',
                        image: newNode.image,
                        editable: false,
                        resizable: true,
                        verticalLabelPosition: 'bottom',
                        spacingTop: -15,
                    },
                });

                for (const edge of edgesToTarget) {
                    graph.insertEdge({
                        parent,
                        value: edge.name,
                        source: cell,
                        target: vertex,
                        style: buildEdgeStyle(edge),
                    });
                }
            }
        }

        // Tous les liens entre objets déjà présents dans le graphe doivent
        // être ajoutés, pas seulement ceux touchant le nœud double-cliqué.
        completeMissingEdgesAmongPlacedNodes(parent);
    });
    refreshParallelEdges();
    startPhysics();
});

//-------------------------------------------------------------------------
// Déploiement récursif (bouton Deploy)

function insertPlacedVertex(node: MapNode, position: Pt, parent: Cell): Cell {
    return graph.insertVertex({
        parent,
        id: node.id,
        value: buildLabel(node),
        position: [position.x - 16, position.y - 16],
        size: [32, 32],
        style: {
            shape: 'image',
            image: node.image,
            editable: false,
            resizable: true,
            verticalLabelPosition: 'bottom',
            spacingTop: -15,
        },
    });
}

function deployFromNode(
    nodeId: string,
    depth: number,
    visited: Set<string>,
    filter: string[],
    attrFilter: string[],
    direction: number,
    parent: Cell,
): void {
    if (depth <= 0 || visited.has(nodeId)) return;
    visited.add(nodeId);

    const node = _nodes.get(nodeId);
    const sourceCell = model.getCell(nodeId) as Cell | null;
    const geom = sourceCell?.getGeometry();
    if (!node || !sourceCell || !geom) return;

    const newEdgesByTarget = new Map<string, Edge[]>();
    for (const edge of node.edges) {
        if (model.getCell(edge.attachedNodeId)) continue; // déjà affiché
        const targetNode = _nodes.get(edge.attachedNodeId);
        if (!targetNode) continue;

        const passesFilter =
            filter.length === 0 ||
            filter.includes(targetNode.vue) ||
            (filter.includes('8') && edge.edgeType === 'CABLE') ||
            (filter.includes('9') && edge.edgeType === 'FLUX');

        if (
            !passesFilter ||
            !matchesAttrFilter(targetNode, attrFilter) ||
            !matchesDirection(direction, node, targetNode)
        ) continue;

        const list = newEdgesByTarget.get(edge.attachedNodeId);
        if (list) list.push(edge); else newEdgesByTarget.set(edge.attachedNodeId, [edge]);
    }

    if (newEdgesByTarget.size === 0) return;

    const targetIds = Array.from(newEdgesByTarget.keys());
    const positions = placeObjectsOnCircle({x: geom.x, y: geom.y}, 80, targetIds.length);

    targetIds.forEach((attachedNodeId, i) => {
        const edgesToTarget = newEdgesByTarget.get(attachedNodeId)!;
        const newNode = _nodes.get(attachedNodeId);
        if (!newNode) return;

        const vertex = insertPlacedVertex(newNode, positions[i], parent);

        for (const edge of edgesToTarget) {
            graph.insertEdge({
                parent,
                value: edge.name,
                source: sourceCell,
                target: vertex,
                style: buildEdgeStyle(edge),
            });
        }
    });

    for (const attachedNodeId of targetIds) {
        deployFromNode(attachedNodeId, depth - 1, visited, filter, attrFilter, direction, parent);
    }
}

document.getElementById('deploy-btn')?.addEventListener('click', (e) => {
    const selected = graph.getSelectionCell() as Cell | null;
    if (!selected || !_nodes.has(selected.id as string)) {
        const message = (e.currentTarget as HTMLElement).dataset.pleaseSelect ?? 'Please select a node.';
        alert(message);
        return;
    }

    const depthSelect = document.getElementById('depth') as HTMLSelectElement | null;
    const depth = parseInt(depthSelect?.value ?? '3', 10) || 3;

    graph.batchUpdate(() => {
        const parent = graph.getDefaultParent();
        deployFromNode(selected.id as string, depth, new Set(), getFilter(), getAttrFilter(), getDirection(), parent);
        completeMissingEdgesAmongPlacedNodes(parent);
    });
    refreshParallelEdges();
    startPhysics();
});

//-------------------------------------------------------------------------
// Ajout direct d'un objet sélectionné dans le sélecteur (#node)

document.getElementById('add-node-btn')?.addEventListener('click', () => {
    const nodeId = nodeSelector?.value;
    if (!nodeId) return;

    if (model.getCell(nodeId)) {
        graph.setSelectionCells([model.getCell(nodeId) as Cell]);
        return;
    }

    const node = _nodes.get(nodeId);
    if (!node) return;

    graph.batchUpdate(() => {
        const parent = graph.getDefaultParent();

        // Centre de la partie VISIBLE du graphe (et non de son contenu, qui
        // peut être vide ou hors champ), converti en coordonnées modèle.
        const view = graph.getView();
        const center = {
            x: container.clientWidth / 2 / view.scale - view.translate.x,
            y: container.clientHeight / 2 / view.scale - view.translate.y,
        };

        const newVertex = graph.insertVertex({
            parent,
            id: node.id,
            value: buildLabel(node),
            position: [center.x - 16, center.y - 16],
            size: [32, 32],
            style: {
                shape: 'image',
                image: node.image,
                editable: false,
                resizable: true,
                verticalLabelPosition: 'bottom',
                spacingTop: -15,
            },
        });

        completeMissingEdgesAmongPlacedNodes(parent);
        graph.setSelectionCell(newVertex);
    });
    refreshParallelEdges();
});

//-------------------------------------------------------------------------
// Réinitialisation du canevas

document.getElementById('reload-btn')?.addEventListener('click', () => {
    const cells = graph.getChildCells().filter((c) => !isBackgroundCell(c));
    if (cells.length > 0) graph.removeCells(cells);
});

//-------------------------------------------------------------------------
// Mise à jour des nœuds depuis _nodes

// Retrouve les données d'un lien (JSON _nodes) à partir de ses deux extrémités
function findEdgeData(sourceId: string, targetId: string): Edge | undefined {
    return (
        _nodes.get(sourceId)?.edges.find((e) => e.attachedNodeId === targetId) ??
        _nodes.get(targetId)?.edges.find((e) => e.attachedNodeId === sourceId)
    );
}

document.getElementById('update-btn')?.addEventListener('click', () => {
    graph.batchUpdate(() => {
        graph.getChildCells().forEach((cell) => {
            if (cell.isEdge()) {
                const s = cell.source, t = cell.target;
                if (!s || !t) return;
                const data = findEdgeData(String(s.id), String(t.id));
                if (!data || data.edgeType !== 'CABLE') return;

                // Un lien physique conserve sa couleur et son type lors de la mise à jour.
                cell.value = data.name;
                const style: CellStateStyle = cell.style ?? {};
                style.strokeColor = data.color ?? '#000000';
                style.strokeWidth = 2;
                cell.style = style;
                return;
            }
            const style = styleOf(cell);
            if (style?.isBackground) return;
            if (!style?.image) return;

            const node = _nodes.get(cell.id as string);
            if (!node) {
                graph.removeCells([cell], true);
            } else {
                cell.value = buildLabel(node);
                styleUtils.setCellStyles(graph.getDataModel(), [cell], 'image', node.image);
            }
        });
        graph.refresh();
    });
});

//---------------------------------------------------------------------------
// Export SVG

const svgElement = graph.container.querySelector('svg') as SVGSVGElement | null;

async function embedImagesInSVG(svg: SVGSVGElement): Promise<void> {
    const images = Array.from(svg.querySelectorAll('image'));
    await Promise.all(images.map(async (img) => {
        const href = img.getAttribute('xlink:href') ?? img.getAttribute('href');
        if (!href || href.startsWith('data:')) return;
        try {
            const response = await fetch(href);
            const blob = await response.blob();
            const dataUrl = await new Promise<string>((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result as string);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
            img.setAttribute('href', dataUrl);
            img.removeAttribute('xlink:href');
        } catch (err) {
            console.error('Erreur embed image SVG', err);
        }
    }));
}

async function downloadSVG(): Promise<void> {
    if (!svgElement) {
        alert('SVG introuvable');
        return;
    }

    await embedImagesInSVG(svgElement);

    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(svgElement);
    const blob = new Blob([svgString], {type: 'image/svg+xml;charset=utf-8'});
    const url = URL.createObjectURL(blob);

    const now = new Date();
    const timestamp =
        now.getFullYear() +
        String(now.getMonth() + 1).padStart(2, '0') +
        String(now.getDate()).padStart(2, '0') +
        String(now.getHours()).padStart(2, '0') +
        String(now.getMinutes()).padStart(2, '0');

    const link = document.createElement('a');
    link.href = url;
    link.download = `graph-${timestamp}.svg`;
    link.click();
    URL.revokeObjectURL(url);
}

document.getElementById('download-btn')?.addEventListener('click', downloadSVG);

//---------------------------------------------------------------------------
// Moteur physique (force-directed) start / stop
//---------------------------------------------------------------------------

let physicsRunning = false;
let physicsRafId: number | null = null;
const physicsStartGeo = new Map<Cell, Geometry>();

// Distance minimale entre un nœud confiné et le bord de son groupe
const GROUP_EDGE_MARGIN = 10;

// Le label (sous l'objet, verticalLabelPosition: 'bottom') peut être sur
// plusieurs lignes (IP, tags) : on lit sa bounding box réellement rendue
// (gère le retour à la ligne, la police et le spacingTop sans approximation)
// pour calculer jusqu'où le pied de l'objet (icône + label) descend.
// Police et décalage réellement appliqués au label des objets (cf. styles
// d'insertion des sommets image : pas de fontSize explicite => défaut
// MaxGraph 11px ; spacingTop: -15 remonte le label dans l'icône).
const LABEL_FONT_SIZE = 11;
const LABEL_LINE_HEIGHT = LABEL_FONT_SIZE * 4;
const LABEL_SPACING_TOP = -15;

function getFootprintHeight(cell: Cell, geo: Geometry): number {
    const value = cell.value;
    if (typeof value !== 'string' || value.length === 0) return geo.height;

    const lines = value.split('\n').length;
    const labelHeight = lines * LABEL_LINE_HEIGHT;
    const extra = Math.max(0, labelHeight + LABEL_SPACING_TOP);

    return geo.height + extra;
}

// Arrêt automatique : mouvement (px/tick) sous ce seuil pendant N ticks de suite
const STABILITY_THRESHOLD = 0.4;
const STABILITY_TICKS_REQUIRED = 30;
let stableTicks = 0;

function isMovableObject(cell: Cell): boolean {
    if (!cell.isVertex()) return false;
    const s = styleOf(cell);
    const hasImage = s?.shape === 'image' && !!s?.image;
    const hasChildren = (cell.children?.length ?? 0) > 0;
    return hasImage && !hasChildren && !s?.isBackground;
}

function collectVertices(): Cell[] {
    const acc: Cell[] = [];
    const walk = (cell: Cell) => {
        for (const c of cell.children ?? []) {
            if (c.isVertex()) acc.push(c);
            if (c.children?.length) walk(c);
        }
    };
    walk(graph.getDefaultParent());
    return acc;
}

function physicsTick(): void {
    // Les conteneurs de groupe ne sont pas de vrais objets : les exclure des
    // forces de répulsion, sinon ils repoussent leurs propres enfants vers
    // les bords du groupe.
    const all = collectVertices().filter((c) => (c.children?.length ?? 0) === 0);
    const movable = all.filter(isMovableObject);
    if (movable.length === 0) {
        stopPhysics();
        return;
    }

    const pos = new Map<Cell, Point>();
    for (const v of all) pos.set(v, modelCenter(v));
    const force = new Map<Cell, Point>();
    for (const v of movable) force.set(v, new Point(0, 0));

    // Vitesse de réaction +50%
    const K_REP = 11500, K_SPRING = 0.12, REST = 60, DAMPING = 0.85, MAX_STEP = 30;

    for (const v of movable) {
        const pv = pos.get(v)!, f = force.get(v)!;
        for (const u of all) {
            if (u === v) continue;
            const pu = pos.get(u)!;
            const dx = pv.x - pu.x, dy = pv.y - pu.y;
            let d2 = dx * dx + dy * dy;
            if (d2 < 1) d2 = 1;
            const d = Math.sqrt(d2), rep = K_REP / d2;
            f.x += (dx / d) * rep;
            f.y += (dy / d) * rep;
        }
    }
    for (const edge of graph.getChildEdges(graph.getDefaultParent())) {
        const s = edge.source, t = edge.target;
        if (!s || !t) continue;
        const ps = pos.get(s) ?? modelCenter(s), pt = pos.get(t) ?? modelCenter(t);
        const dx = pt.x - ps.x, dy = pt.y - ps.y;
        const d = Math.hypot(dx, dy) || 1, fmag = K_SPRING * (d - REST);
        const fx = (dx / d) * fmag, fy = (dy / d) * fmag;

        // Un lien qui sort d'un groupe ne doit pas influencer l'élément
        // qui se trouve dans ce groupe (seuls les liens internes au même
        // groupe agissent sur ses membres).
        const sParent = s.getParent(), tParent = t.getParent();
        const sConfined = !!sParent && sParent !== graph.getDefaultParent();
        const tConfined = !!tParent && tParent !== graph.getDefaultParent();
        const externalToS = sConfined && sParent !== tParent;
        const externalToT = tConfined && tParent !== sParent;

        if (force.has(s) && !externalToS) {
            const f = force.get(s)!;
            f.x += fx;
            f.y += fy;
        }
        if (force.has(t) && !externalToT) {
            const f = force.get(t)!;
            f.x -= fx;
            f.y -= fy;
        }
    }

    // Rappel vers le centre du groupe pour les objets confinés : sans cette
    // force, la répulsion pure n'a rien pour la contrebalancer et envoie
    // systématiquement les objets contre les bords du groupe.
    const K_CENTER = 0.02;
    for (const v of movable) {
        const p = v.getParent();
        if (!p || p === graph.getDefaultParent()) continue;
        const pg = p.getGeometry();
        const geo0 = v.getGeometry();
        if (!pg || !geo0) continue;
        const f = force.get(v)!;
        const targetX = (pg.width - geo0.width) / 2;
        const targetY = (pg.height - geo0.height) / 2;
        f.x += (targetX - geo0.x) * K_CENTER;
        f.y += (targetY - geo0.y) * K_CENTER;
    }

    // Groupes existants : un nœud qui n'en fait pas partie ne doit jamais y
    // entrer et doit rester à au moins 10px de son bord.
    const groups = graph.getChildVertices(graph.getDefaultParent()).filter((c) => (c.children?.length ?? 0) > 0);

    let maxMovement = 0;

    graph.batchUpdate(() => {       // n'empile rien tant que physicsSuppressUndo est vrai
        for (const v of movable) {
            const f = force.get(v)!;
            let dx = f.x * DAMPING, dy = f.y * DAMPING;
            const m = Math.hypot(dx, dy);
            if (m > maxMovement) maxMovement = m;
            if (m > MAX_STEP) {
                dx = dx / m * MAX_STEP;
                dy = dy / m * MAX_STEP;
            }
            const geo = v.getGeometry()?.clone();
            if (!geo) continue;
            geo.x += dx;
            geo.y += dy;
            const footprintHeight = getFootprintHeight(v, geo);

            const p = v.getParent();
            if (p && p !== graph.getDefaultParent()) {
                const pg = p.getGeometry();
                if (pg) {
                    // L'icône doit garder 10px du bord du groupe ; le label sous
                    // l'objet (potentiellement multi-lignes) peut s'en approcher
                    // davantage, tant qu'il reste entièrement dans le groupe.
                    const minX = GROUP_EDGE_MARGIN, maxX = Math.max(minX, pg.width - geo.width - GROUP_EDGE_MARGIN);
                    const minY = GROUP_EDGE_MARGIN;
                    const maxY = Math.max(minY, pg.height - Math.max(geo.height + GROUP_EDGE_MARGIN, footprintHeight));
                    geo.x = Math.max(minX, Math.min(geo.x, maxX));
                    geo.y = Math.max(minY, Math.min(geo.y, maxY));
                }
            } else {
                // Un nœud hors groupe ne doit jamais entrer dans un groupe
                // existant : on l'éjecte vers le bord le plus proche, à 10px.
                for (const group of groups) {
                    const gg = group.getGeometry();
                    if (!gg) continue;
                    const gx = gg.x - GROUP_EDGE_MARGIN, gy = gg.y - GROUP_EDGE_MARGIN;
                    const gw = gg.width + 2 * GROUP_EDGE_MARGIN, gh = gg.height + 2 * GROUP_EDGE_MARGIN;

                    const overlapLeft = (geo.x + geo.width) - gx;
                    const overlapRight = (gx + gw) - geo.x;
                    const overlapTop = (geo.y + footprintHeight) - gy;
                    const overlapBottom = (gy + gh) - geo.y;

                    if (overlapLeft > 0 && overlapRight > 0 && overlapTop > 0 && overlapBottom > 0) {
                        const minOverlap = Math.min(overlapLeft, overlapRight, overlapTop, overlapBottom);
                        if (minOverlap === overlapLeft) geo.x = gx - geo.width;
                        else if (minOverlap === overlapRight) geo.x = gx + gw;
                        else if (minOverlap === overlapTop) geo.y = gy - footprintHeight;
                        else geo.y = gy + gh;
                    }
                }
            }
            v.setGeometry(geo);
        }
    });
    graph.refresh();

    // Arrêt automatique une fois le graphe stabilisé (plus de mouvement notable).
    if (maxMovement < STABILITY_THRESHOLD) {
        stableTicks++;
        if (stableTicks >= STABILITY_TICKS_REQUIRED) {
            stopPhysics();
            return;
        }
    } else {
        stableTicks = 0;
    }

    if (physicsRunning) physicsRafId = requestAnimationFrame(physicsTick);
}

function setPhysicsButtonActive(active: boolean): void {
    const btn = document.getElementById('physics-btn');
    if (!btn) return;
    btn.setAttribute('aria-pressed', String(active));
    btn.classList.toggle('bi-magnet', !active);
    btn.classList.toggle('bi-magnet-fill', active);
}

function startPhysics(): void {
    if (physicsRunning) return;
    physicsStartGeo.clear();
    stableTicks = 0;
    for (const v of collectVertices().filter(isMovableObject)) {
        const g = v.getGeometry();
        if (g) physicsStartGeo.set(v, g.clone());
    }
    physicsSuppressUndo = true;     // les ticks ne créent aucun état
    physicsRunning = true;
    setPhysicsButtonActive(true);
    physicsRafId = requestAnimationFrame(physicsTick);
}

function stopPhysics(): void {
    physicsRunning = false;
    if (physicsRafId !== null) {
        cancelAnimationFrame(physicsRafId);
        physicsRafId = null;
    }
    setPhysicsButtonActive(false);

    if (physicsStartGeo.size === 0) {
        physicsSuppressUndo = false;
        return;
    }

    // Positions finales atteintes
    const finalGeo = new Map<Cell, Geometry>();
    for (const cell of physicsStartGeo.keys()) {
        const g = cell.getGeometry();
        if (g) finalGeo.set(cell, g.clone());
    }
    // (a) revenir au départ SANS enregistrer (drapeau encore actif)
    graph.batchUpdate(() => {
        for (const [cell, g0] of physicsStartGeo) cell.setGeometry(g0.clone());
    });
    // (b) ré-appliquer le final EN enregistrant → un seul état undo
    physicsSuppressUndo = false;
    graph.batchUpdate(() => {
        for (const [cell, g1] of finalGeo) cell.setGeometry(g1);
        refreshParallelEdges();     // courbures incluses dans la même transaction
    });
    graph.refresh();
    physicsStartGeo.clear();
}

document.getElementById('physics-btn')?.addEventListener('click', () => {
    physicsRunning ? stopPhysics() : startPhysics();
});

// Toute interaction manuelle (clic, déplacement) doit arrêter le moteur
// physique au préalable : sinon physicsSuppressUndo, encore actif pendant
// l'animation, avale aussi l'édition undo-able de l'utilisateur (le ctrl+Z
// du déplacement manuel ne fonctionnait plus tant que la physique tournait).
graph.addMouseListener({
    mouseDown(_sender, _me) {
        if (physicsRunning) stopPhysics();
    },
    mouseMove(_sender, _me) {
    },
    mouseUp(_sender, _me) {
    },
});

//---------------------------------------------------------------------------
// Arrière-plan de carte
//---------------------------------------------------------------------------

const BACKGROUND_ID = '__background__';

function setBackground(image: string, w: number, h: number): void {
    graph.batchUpdate(() => {
        const parent = graph.getDefaultParent();
        let bg = model.getCell(BACKGROUND_ID) as Cell | null;
        if (bg) {
            const style: AppCellStyle = bg.style ?? {};
            style.image = image;
            bg.style = style;
            const geo = bg.getGeometry()?.clone();
            if (geo) {
                geo.width = w;
                geo.height = h;
                bg.setGeometry(geo);
            }
        } else {
            bg = graph.insertVertex({
                parent, id: BACKGROUND_ID, value: '', position: [0, 0], size: [w, h],
                style: {shape: 'image', image, isBackground: true, editable: false} as AppCellStyle,
            });
        }
        graph.orderCells(true, [bg!]);
    });
    graph.refresh();
}

function applyBackgroundFromSource(src: string): void {
    const img = new Image();
    img.onload = () => setBackground(src, img.naturalWidth, img.naturalHeight);
    img.src = src;
}

function selectDefaultBackground(url: string): void {
    applyBackgroundFromSource(url);
}

const backgroundMenu = document.getElementById('background-menu') as HTMLDivElement | null;

document.getElementById('background-btn')?.addEventListener('click', (e) => {
    if (!backgroundMenu) return;
    const target = e.currentTarget as HTMLElement;
    const rect = target.getBoundingClientRect();

    // position:absolute se positionne par rapport à l'ancêtre positionné
    // (#editor, position:relative), pas par rapport au viewport. On ne peut
    // pas utiliser backgroundMenu.offsetParent ici : il vaut null tant que
    // le menu est display:none, soit précisément au moment de l'ouvrir.
    const editorRect = document.getElementById('editor')?.getBoundingClientRect();
    const offsetLeft = editorRect?.left ?? 0;
    const offsetTop = editorRect?.top ?? 0;

    backgroundMenu.style.left = `${rect.right - offsetLeft + 8}px`;
    backgroundMenu.style.top = `${rect.top - offsetTop}px`;
    backgroundMenu.style.display = backgroundMenu.style.display === 'block' ? 'none' : 'block';
});

document.querySelectorAll<HTMLElement>('.background-thumb').forEach((thumb) => {
    thumb.addEventListener('click', () => {
        const url = thumb.dataset.url;
        if (url) selectDefaultBackground(url);
        if (backgroundMenu) backgroundMenu.style.display = 'none';
    });
});

document.getElementById('background-input-btn')?.addEventListener('click', () => {
    document.getElementById('background-input')?.click();
});

document.getElementById('background-input')?.addEventListener('change', (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        applyBackgroundFromSource(reader.result as string);
        if (backgroundMenu) backgroundMenu.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

document.getElementById('background-remove-btn')?.addEventListener('click', () => {
    const bg = model.getCell(BACKGROUND_ID) as Cell | null;
    if (bg) graph.removeCells([bg], true);
    if (backgroundMenu) backgroundMenu.style.display = 'none';
});

document.addEventListener('click', (event) => {
    const target = event.target as globalThis.Node | null;
    if (
        backgroundMenu &&
        !backgroundMenu.contains(target) &&
        target !== document.getElementById('background-btn')
    ) {
        backgroundMenu.style.display = 'none';
    }
});
