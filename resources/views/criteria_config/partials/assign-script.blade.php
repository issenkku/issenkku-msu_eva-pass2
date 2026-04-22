{{-- script ของหน้า assign ดูแลการ submit แบบ mock, การเพิ่ม template แบบ dynamic และการ reset ฟอร์ม --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ฟอร์มหลักถูกใช้เป็นจุดรวม event เพื่อให้เปลี่ยน endpoint ภายหลังได้ง่าย
        const form = document.getElementById('assignment-form');

        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
            });
        }

        const quantAddButton = document.getElementById('add_quantitative_item_btn');
        const quantContainer = document.getElementById('quantitative_items_container');
        const quantTemplate = document.getElementById('quantitative_item_template');

        if (quantAddButton && quantContainer && quantTemplate) {
            // เพิ่มรายการด้านปริมาณจาก template เดิม โดยไม่สร้าง wrapper ซ้ำ
            quantAddButton.addEventListener('click', function() {
                const clone = quantTemplate.content.cloneNode(true);
                quantContainer.appendChild(clone);
            });
        }

        const mainTopicsContainer = document.getElementById('main_topics_container');
        const addMainTopicButton = document.getElementById('add_main_topic_btn');
        const mainTopicTemplate = document.getElementById('main_topic_template');
        const subTopicTemplate = document.getElementById('sub_topic_template');
        const itemTemplate = document.getElementById('qualitative_item_template');

        if (mainTopicsContainer) {
            // ใช้ event delegation เพราะรายการหัวข้อย่อยและรายการประเมินถูกสร้างเพิ่มภายหลัง
            mainTopicsContainer.addEventListener('click', function(event) {
                const target = event.target;

                if (target.classList.contains('add_sub_topic_btn')) {
                    const mainTopicBlock = target.closest('.main-topic-block');
                    const subTopicsContainer = mainTopicBlock.querySelector('.sub_topics_container');
                    const mainIndex = Array.from(mainTopicsContainer.children).indexOf(mainTopicBlock);
                    const subIndex = subTopicsContainer.children.length;

                    const templateContent = subTopicTemplate.innerHTML
                        .replace(/__MAIN_INDEX__/g, mainIndex)
                        .replace(/__SUB_INDEX__/g, subIndex);

                    const newSubTopic = document.createElement('div');
                    newSubTopic.innerHTML = templateContent;
                    subTopicsContainer.appendChild(newSubTopic.firstElementChild);
                }

                if (target.classList.contains('add_qualitative_item_btn')) {
                    const mainTopicBlock = target.closest('.main-topic-block');
                    const subTopicBlock = target.closest('.sub-topic-block');
                    const itemsContainer = subTopicBlock.querySelector('.qualitative_items_container');
                    const mainIndex = Array.from(mainTopicsContainer.children).indexOf(mainTopicBlock);
                    const subIndex = Array.from(mainTopicBlock.querySelector('.sub_topics_container').children).indexOf(subTopicBlock);
                    const itemIndex = itemsContainer.children.length;

                    const templateContent = itemTemplate.innerHTML
                        .replace(/__MAIN_INDEX__/g, mainIndex)
                        .replace(/__SUB_INDEX__/g, subIndex)
                        .replace(/__ITEM_INDEX__/g, itemIndex);

                    const newItem = document.createElement('div');
                    newItem.innerHTML = templateContent;
                    itemsContainer.appendChild(newItem.firstElementChild);
                }
            });
        }

        if (addMainTopicButton && mainTopicsContainer && mainTopicTemplate) {
            addMainTopicButton.addEventListener('click', function() {
                const mainIndex = mainTopicsContainer.children.length;
                const templateContent = mainTopicTemplate.innerHTML.replace(/__MAIN_INDEX__/g, mainIndex);
                const newMainTopic = document.createElement('div');
                newMainTopic.innerHTML = templateContent;
                mainTopicsContainer.appendChild(newMainTopic.firstElementChild);
            });
        }

        const resetFormButton = document.getElementById('reset_form_btn');

        if (resetFormButton) {
            // reset จะล้างทั้งค่าฟอร์มและ element แบบ dynamic ให้กลับสู่ state เริ่มต้น
            resetFormButton.addEventListener('click', function() {
                if (!confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                    return;
                }

                if (form) {
                    form.reset();
                }

                const quantitativeContainer = document.getElementById('quantitative_items_container');
                if (quantitativeContainer) {
                    const initialQuantItems = 2;
                    while (quantitativeContainer.children.length > initialQuantItems) {
                        quantitativeContainer.lastChild.remove();
                    }
                }

                if (mainTopicsContainer) {
                    const initialMainTopics = 1;
                    while (mainTopicsContainer.children.length > initialMainTopics) {
                        mainTopicsContainer.lastChild.remove();
                    }

                    const firstMainTopic = mainTopicsContainer.querySelector('.main-topic-block');
                    if (firstMainTopic) {
                        const subTopicsContainer = firstMainTopic.querySelector('.sub_topics_container');
                        const initialSubTopics = 1;
                        while (subTopicsContainer.children.length > initialSubTopics) {
                            subTopicsContainer.lastChild.remove();
                        }

                        const firstSubTopic = subTopicsContainer.querySelector('.sub-topic-block');
                        if (firstSubTopic) {
                            const itemsContainer = firstSubTopic.querySelector('.qualitative_items_container');
                            const initialItems = 1;
                            while (itemsContainer.children.length > initialItems) {
                                itemsContainer.lastChild.remove();
                            }
                        }
                    }
                }

                alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
            });
        }
    });
</script>
