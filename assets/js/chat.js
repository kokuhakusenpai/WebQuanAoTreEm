// Hiển thị/ẩn chatbox
function toggleChat() {
    const chatBox = document.getElementById("chatBox");
    chatBox.style.display =
      chatBox.style.display === "none" || chatBox.style.display === ""
        ? "block"
        : "none";
  }

  function sendMessage() {
    const userInput = document.getElementById("userInput").value;
    const chatBody = document.getElementById("chatBody");

    if (userInput.trim() === "") return;

    chatBody.innerHTML += `<p><strong>Bạn:</strong> ${userInput}</p>`;
    document.getElementById("userInput").value = "";

    setTimeout(() => {
      chatBody.innerHTML += `<p><strong>Bot:</strong> ${getResponse(
        userInput
      )}</p>`;
      chatBody.scrollTop = chatBody.scrollHeight;
    }, 500);
  }

function getResponse(question) {
const faq = {
    "shop có miễn phí vận chuyển không?": "Shop miễn phí vận chuyển cho đơn hàng từ 500.000đ trở lên.",
    "thời gian giao hàng bao lâu?": "Thời gian giao hàng từ 2-5 ngày tùy khu vực.",
    "shop có đổi trả không?": "Shop hỗ trợ đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.",
    "shop có size cho bé 2 tuổi không?": "Shop có đầy đủ size cho bé từ sơ sinh đến 12 tuổi.",
    "cách đặt hàng thế nào?": "Bạn có thể đặt hàng trực tiếp trên website hoặc inbox fanpage của shop."
};

return faq[question.toLowerCase()] || "Xin lỗi, shop chưa có thông tin cho câu hỏi này. Bạn có thể liên hệ trực tiếp để được hỗ trợ!";
}