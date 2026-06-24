import {
    Graph,
    GraphDataModel,
    InternalEvent,
    ModelXmlSerializer,
} from '@maxgraph/core';

//-----------------------------------------------------------------------
// Interfaces métier

interface MapNode {
    id: string;
    vue: string;
    label: string;
    image: string;
    type: string;
}

type NodeMap = Map<string, MapNode>;

declare const _nodes: NodeMap;

//-----------------------------------------------------------------------
// Initialisation du graphe

const container = document.getElementById('graph-container') as HTMLDivElement | null;
if (!container) {
    throw new Error('#graph-container introuvable');
}

const graph = new Graph(container, new GraphDataModel());
const model = graph.getDataModel();

graph.setEnabled(false);
InternalEvent.disableContextMenu(container);

//-----------------------------------------------------------------------
// Style des sommets

graph.getStylesheet().getDefaultVertexStyle().cursor = 'pointer';
graph.options.expandedImage = null;

//-----------------------------------------------------------------------
// Style des arêtes

const edgeDefaultStyle = graph.getStylesheet().getDefaultEdgeStyle();
edgeDefaultStyle.labelBackgroundColor = '#FFFFFF';
edgeDefaultStyle.strokeWidth  = 2;
edgeDefaultStyle.rounded      = false;
edgeDefaultStyle.entryPerimeter = false;

//-------------------------------------------------------------------------
// LOAD

const FIT_MARGIN = 20;

// Zoom pour que tous les objets soient visibles, alignés en haut à gauche du
// conteneur. Calcul manuel (plutôt que FitPlugin.fit()) pour garder un
// contrôle exact en pixels sur la marge, quel que soit le niveau de zoom.
function fitTopLeft(margin: number): void {
    const view   = graph.getView();
    const bounds = graph.getGraphBounds();
    if (!bounds || bounds.width <= 0 || bounds.height <= 0) return;

    const originalScale = view.scale || 1;
    const modelWidth     = bounds.width  / originalScale;
    const modelHeight    = bounds.height / originalScale;

    const availableWidth  = container!.clientWidth  - margin * 2;
    const availableHeight = container!.clientHeight - margin * 2;

    let newScale = Math.min(availableWidth / modelWidth, availableHeight / modelHeight);
    if (!Number.isFinite(newScale) || newScale <= 0) newScale = 1;

    const modelLeft = bounds.x / originalScale - view.translate.x;
    const modelTop  = bounds.y / originalScale - view.translate.y;

    const translateX = margin / newScale - modelLeft;
    const translateY = margin / newScale - modelTop;

    view.scaleAndTranslate(newScale, translateX, translateY);
}

export function loadGraph(xml: string): void {
    new ModelXmlSerializer(model).import(xml);

    // Le rendu doit être validé avant de mesurer les limites du graphe,
    // sinon getGraphBounds() est vide et le calcul de zoom ne fait rien.
    graph.refresh();

    fitTopLeft(FIT_MARGIN);
}

(window as any).loadGraph = loadGraph;

//--------------------------------------------------------------------------
// Navigation au clic sur un sommet

graph.addListener(InternalEvent.CLICK, (_sender, evt) => {
    const cell = evt.getProperty('cell');
    if (!cell?.isVertex()) return;

    const node = _nodes.get(cell.id as string);
    if (!node) return;

    const id = (cell.id as string).split('_').pop();
    if (id === undefined) return;

    window.location.href = `/admin/${node.type}/${id}`;
});

//-----------------------------------------------------------------------
// Curseur pointeur sur les sommets image

graph.addMouseListener({
    mouseMove(_sender, me) {
        const cell = me.getCell();
        const isImageVertex = cell?.isVertex() && (cell.style as any)?.image != null && !(cell.style as any)?.isBackground;
        container!.style.cursor = isImageVertex ? 'pointer' : 'default';
    },
    mouseDown(_sender, _me) {},
    mouseUp(_sender, _me)   {},
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
            const blob     = await response.blob();
            const dataUrl  = await new Promise<string>((resolve, reject) => {
                const reader     = new FileReader();
                reader.onloadend = () => resolve(reader.result as string);
                reader.onerror   = reject;
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
    const svgString  = serializer.serializeToString(svgElement);
    const blob       = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
    const url        = URL.createObjectURL(blob);

    const now = new Date();
    const timestamp =
        now.getFullYear() +
        String(now.getMonth() + 1).padStart(2, '0') +
        String(now.getDate()).padStart(2, '0') +
        String(now.getHours()).padStart(2, '0') +
        String(now.getMinutes()).padStart(2, '0');

    const link    = document.createElement('a');
    link.href     = url;
    link.download = `graph-${timestamp}.svg`;
    link.click();
    URL.revokeObjectURL(url);
}

document.getElementById('download-btn')?.addEventListener('click', downloadSVG);
